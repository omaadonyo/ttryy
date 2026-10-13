<?php

use App\Models\LaunchSignup;
use App\Models\PackageOrder;
use App\Models\User;
use App\Services\IotecPay;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

function iotecConfig(): void
{
    config()->set('services.iotec.client_id', 'test-client');
    config()->set('services.iotec.client_secret', 'test-secret');
    config()->set('services.iotec.wallet_id', '5e83b187-801e-410e-b76e-f491928547e0');
}

function iotecTokenFake(): void
{
    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
    ]);
}

beforeEach(function () {
    Cache::flush();
    iotecConfig();
});

test('ugandan phone numbers normalize to MSISDN', function () {
    expect(IotecPay::normalizePhone('0772 123456'))->toBe('256772123456')
        ->and(IotecPay::normalizePhone('+256 700 000000'))->toBe('256700000000')
        ->and(IotecPay::normalizePhone('256752111222'))->toBe('256752111222')
        ->and(IotecPay::normalizePhone('752111222'))->toBe('256752111222')
        ->and(IotecPay::normalizePhone('12345'))->toBeNull()
        ->and(IotecPay::normalizePhone('not a number'))->toBeNull();
});

test('checkout pay starts a mobile money prompt', function () {
    iotecTokenFake();
    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
        'pay.iotec.io/api/collections/collect' => Http::response(['id' => '11111111-1111-4111-8111-111111111111', 'status' => 'Pending', 'externalId' => 'TTRYY-AAAAAA', 'amount' => 59000]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'domain' => 'budget',
        'duration_months' => 12,
        'business_name' => 'Test Biz',
        'phone' => '+256 772 000001',
    ]);

    $order = PackageOrder::first();

    $this->actingAs($user)->postJson('/dashboard/checkout/iotec/pay', [
        'reference' => $order->reference,
        'phone' => '0772 000001',
    ])->assertOk()->assertJsonPath('tx_id', '11111111-1111-4111-8111-111111111111');

    expect($order->fresh()->gateway_tx_id)->toBe('11111111-1111-4111-8111-111111111111')
        ->and($order->fresh()->payment_method)->toBe('iotec');
});

test('checkout pay rejects invalid phone numbers', function () {
    iotecTokenFake();

    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'START',
        'billing_frequency' => 'full',
        'domain' => 'none',
        'business_name' => 'Test Biz',
        'phone' => '+256 772 000002',
    ]);

    $order = PackageOrder::first();

    $this->actingAs($user)->postJson('/dashboard/checkout/iotec/pay', [
        'reference' => $order->reference,
        'phone' => 'abc',
    ])->assertStatus(422)->assertJsonPath('message', 'That phone number does not look like a valid Ugandan mobile number. Use e.g. 0772 123456.');
});

test('status poll marks the order paid on success', function () {
    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
        'pay.iotec.io/api/collections/status/*' => Http::response(['id' => '22222222-2222-4222-8222-222222222222', 'status' => 'Success', 'amount' => 59000, 'externalId' => 'TTRYY-BBBBBB']),
    ]);

    $user = User::factory()->create();
    $order = PackageOrder::create([
        'user_id' => $user->id,
        'reference' => 'TTRYY-BBBBBB',
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'domain' => 'budget',
        'duration_months' => 12,
        'periods' => 12,
        'amount_per_period' => 30000,
        'domain_fee' => 29000,
        'total_amount' => 389000,
        'due_today' => 59000,
        'business_name' => 'Test Biz',
        'phone' => '+256 772 000003',
        'gateway_tx_id' => '22222222-2222-4222-8222-222222222222',
        'payment_method' => 'iotec',
    ]);

    $this->actingAs($user)->getJson('/dashboard/checkout/iotec/status?reference='.$order->reference)
        ->assertOk()
        ->assertJsonPath('status', 'paid');

    expect($order->fresh()->status)->toBe('paid')
        ->and($order->fresh()->paid_amount)->toBe(59000);
});

test('status poll reports failure without marking paid', function () {
    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
        'pay.iotec.io/api/collections/status/*' => Http::response(['id' => '33333333-3333-4333-8333-333333333333', 'status' => 'Failed', 'statusMessage' => 'Declined by payer']),
    ]);

    $user = User::factory()->create();
    $order = PackageOrder::create([
        'user_id' => $user->id,
        'reference' => 'TTRYY-CCCCCC',
        'package' => 'START',
        'billing_frequency' => 'full',
        'domain' => 'none',
        'duration_months' => 12,
        'periods' => 1,
        'amount_per_period' => 250000,
        'domain_fee' => 0,
        'total_amount' => 250000,
        'due_today' => 250000,
        'business_name' => 'Test Biz',
        'phone' => '+256 772 000004',
        'gateway_tx_id' => '33333333-3333-4333-8333-333333333333',
        'payment_method' => 'iotec',
    ]);

    $this->actingAs($user)->getJson('/dashboard/checkout/iotec/status?reference='.$order->reference)
        ->assertOk()
        ->assertJsonPath('status', 'failed');

    expect($order->fresh()->status)->not->toBe('paid');
});

test('iotec callback finalizes the order after re-verifying', function () {
    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
        'pay.iotec.io/api/collections/status/*' => Http::response(['id' => '44444444-4444-4444-8444-444444444444', 'status' => 'Success', 'amount' => 21000, 'externalId' => 'TTRYY-DDDDDD']),
    ]);

    $user = User::factory()->create();
    $order = PackageOrder::create([
        'user_id' => $user->id,
        'reference' => 'TTRYY-DDDDDD',
        'package' => 'START',
        'billing_frequency' => 'monthly',
        'domain' => 'none',
        'duration_months' => 12,
        'periods' => 12,
        'amount_per_period' => 21000,
        'domain_fee' => 0,
        'total_amount' => 252000,
        'due_today' => 21000,
        'business_name' => 'Test Biz',
        'phone' => '+256 772 000005',
        'gateway_tx_id' => '44444444-4444-4444-8444-444444444444',
        'payment_method' => 'iotec',
    ]);

    $this->postJson('/api/payments/iotec/callback', [
        'id' => '44444444-4444-4444-8444-444444444444',
        'status' => 'Success',
        'externalId' => 'TTRYY-DDDDDD',
    ])->assertOk();

    expect($order->fresh()->status)->toBe('paid');
});

test('iotec callback rejects a wrong token when one is configured', function () {
    config()->set('services.iotec.callback_token', 'secret-cb-token');

    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
        'pay.iotec.io/api/collections/status/*' => Http::response(['id' => 'x', 'status' => 'Failed']),
    ]);

    $this->postJson('/api/payments/iotec/callback', ['id' => 'x'], ['X-Callback-Token' => 'wrong'])
        ->assertForbidden();

    $this->postJson('/api/payments/iotec/callback', ['id' => 'x'], ['X-Callback-Token' => 'secret-cb-token'])
        ->assertOk();
});

test('launch instant pay creates the application and starts a prompt', function () {
    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
        'pay.iotec.io/api/collections/collect' => Http::response(['id' => '55555555-5555-4555-8555-555555555555', 'status' => 'Pending', 'amount' => 10000]),
    ]);

    $this->postJson('/launch/iotec/pay', [
        'name' => 'Jane Nakato',
        'phone' => '0772 000006',
        'idea_name' => 'FarmFresh',
        'idea' => 'A delivery service for fresh farm produce in Kampala city.',
        'terms' => '1',
    ])->assertOk()->assertJsonPath('tx_id', '55555555-5555-4555-8555-555555555555');

    $signup = LaunchSignup::first();
    expect($signup)->not->toBeNull()
        ->and($signup->gateway_tx_id)->toBe('55555555-5555-4555-8555-555555555555')
        ->and($signup->payment_status)->toBe('pending');
});

test('launch status poll marks the fee paid', function () {
    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
        'pay.iotec.io/api/collections/status/*' => Http::response(['id' => '66666666-6666-4666-8666-666666666666', 'status' => 'Success', 'amount' => 10000]),
    ]);

    $signup = LaunchSignup::create([
        'name' => 'Jane Nakato',
        'phone' => '0772000007',
        'idea_name' => 'FarmFresh',
        'idea' => 'A delivery service for fresh farm produce in Kampala city.',
        'status' => LaunchSignup::STATUS_NEW,
        'payment_status' => 'pending',
        'gateway_tx_id' => '66666666-6666-4666-8666-666666666666',
    ]);

    $this->getJson('/launch/iotec/status?signup='.$signup->id)
        ->assertOk()
        ->assertJsonPath('status', 'paid');

    expect($signup->fresh()->payment_status)->toBe('paid')
        ->and($signup->fresh()->paid_amount)->toBe(10000);
});

test('card return finalizes the order after verifying with iotec', function () {
    Http::fake([
        'id.iotec.io/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 300, 'token_type' => 'Bearer']),
        'pay.iotec.io/api/collections/status/*' => Http::response(['id' => '77777777-7777-4777-8777-777777777777', 'status' => 'Success', 'amount' => 21000, 'externalId' => 'TTRYY-EEEEEE']),
    ]);

    $user = User::factory()->create();
    $order = PackageOrder::create([
        'user_id' => $user->id,
        'reference' => 'TTRYY-EEEEEE',
        'package' => 'START',
        'billing_frequency' => 'monthly',
        'domain' => 'none',
        'duration_months' => 12,
        'periods' => 12,
        'amount_per_period' => 21000,
        'domain_fee' => 0,
        'total_amount' => 252000,
        'due_today' => 21000,
        'business_name' => 'Test Biz',
        'phone' => '+256 772 000008',
        'gateway_tx_id' => '77777777-7777-4777-8777-777777777777',
        'payment_method' => 'iotec',
    ]);

    $this->actingAs($user)->get('/payments/iotec/card-return?id=77777777-7777-4777-8777-777777777777&status=Success')
        ->assertRedirect(route('checkout.success', $order));

    expect($order->fresh()->status)->toBe('paid');
});

test('payments fail cleanly when iotec is not configured', function () {
    config()->set('services.iotec.client_id', null);
    config()->set('services.iotec.client_secret', null);

    $user = User::factory()->create();
    $order = PackageOrder::create([
        'user_id' => $user->id,
        'reference' => 'TTRYY-FFFFFF',
        'package' => 'START',
        'billing_frequency' => 'full',
        'domain' => 'none',
        'duration_months' => 12,
        'periods' => 1,
        'amount_per_period' => 250000,
        'domain_fee' => 0,
        'total_amount' => 250000,
        'due_today' => 250000,
        'business_name' => 'Test Biz',
        'phone' => '+256 772 000009',
    ]);

    $this->actingAs($user)->postJson('/dashboard/checkout/iotec/pay', [
        'reference' => $order->reference,
        'phone' => '0772 000009',
    ])->assertStatus(422);
});
