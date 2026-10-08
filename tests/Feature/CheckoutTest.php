<?php

use App\Models\PackageOrder;
use App\Models\User;
use Livewire\Livewire;

test('guests can browse checkout but must sign in to order', function () {
    $this->get('/dashboard/checkout?package=GROW')
        ->assertOk()
        ->assertSee('Create an account or log in', false);

    $this->post('/dashboard/checkout', [])->assertRedirect('/login');
});

test('guests can register through the checkout Livewire form', function () {
    Livewire::test(App\Livewire\Checkout\RegisterForm::class)
        ->set('name', 'Jane Nakato')
        ->set('email', 'jane@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('register')
        ->assertRedirect(route('checkout'));

    expect(App\Models\User::where('email', 'jane@example.com')->exists())->toBeTrue();
});

test('guests can log in through the checkout Livewire form', function () {
    $user = User::factory()->create();

    Livewire::test(App\Livewire\Checkout\LoginForm::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('checkout'));

    $this->assertAuthenticatedAs($user);
});

test('corporate monthly orders include the domain one-time fee', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'CORPORATE',
        'billing_frequency' => 'monthly',
        'domain' => 'budget',
        'duration_months' => 6,
        'business_name' => 'Corp Ltd',
        'phone' => '+256 700 000010',
    ]);

    $order = PackageOrder::first();
    expect($order->total_amount)->toBe(117000 * 6 + 29000)
        ->and($order->due_today)->toBe(117000 + 29000);
});

test('checkout page shows the preselected package', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard/checkout?package=BUSINESS')
        ->assertOk()
        ->assertSee('UGX 650,000', false);
});

test('authenticated users can place a monthly package order', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'domain' => 'budget',
        'duration_months' => 12,
        'business_name' => 'Savanna Build Ltd',
        'phone' => '+256 700 000000',
        'niche' => 'Construction',
    ]);

    $order = PackageOrder::first();
    expect($order)->not->toBeNull()
        ->and($order->user_id)->toBe($user->id)
        ->and($order->total_amount)->toBe(30000 * 12 + 29000)
        ->and($order->due_today)->toBe(30000 + 29000)
        ->and($order->domain_fee)->toBe(29000)
        ->and($order->periods)->toBe(12)
        ->and($order->status)->toBe('pending');

    $response->assertRedirect(route('checkout.success', $order));
});

test('pay per day on the START package shows the deposit-first breakdown', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'START',
        'billing_frequency' => 'daily',
        'domain' => 'premium',
        'duration_months' => 3,
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000001',
    ]);

    // 91 daily payments of UGX 700 + UGX 80,000 domain deposit, all due upfront first.
    $order = PackageOrder::first();
    expect($order->total_amount)->toBe(700 * 91 + 80000)
        ->and($order->due_today)->toBe(700 + 80000)
        ->and($order->periods)->toBe(91);
});

test('order totals are computed server-side, not trusted from the client', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'START',
        'billing_frequency' => 'weekly',
        'domain' => 'none',
        'duration_months' => 12,
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000001',
        'total_amount' => 1,
    ]);

    // 52 weekly payments of UGX 4,800, regardless of the tampered field.
    expect(PackageOrder::first()->total_amount)->toBe(4800 * 52);
});

test('full payment always covers a single one-time charge', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'BUSINESS',
        'billing_frequency' => 'full',
        'domain' => 'none',
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000002',
    ]);

    $order = PackageOrder::first();
    expect($order->total_amount)->toBe(650000)
        ->and($order->due_today)->toBe(650000)
        ->and($order->duration_months)->toBe(12);
});

test('users cannot view other users orders', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $order = PackageOrder::create([
        'user_id' => $owner->id,
        'reference' => 'TTRYY-ABCDEF',
        'package' => 'START',
        'billing_frequency' => 'full',
        'duration_months' => 12,
        'periods' => 1,
        'amount_per_period' => 250000,
        'total_amount' => 250000,
        'business_name' => 'Owner Biz',
        'phone' => '+256 700 000003',
    ]);

    $this->actingAs($intruder)->get(route('checkout.success', $order))->assertForbidden();
    $this->actingAs($owner)->get(route('checkout.success', $order))->assertOk();
});

test('my packages page lists the users orders', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'START',
        'billing_frequency' => 'full',
        'domain' => 'none',
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000004',
    ]);

    $this->actingAs($user)->get(route('packages.index'))
        ->assertOk()
        ->assertSee('START')
        ->assertSee('Test Biz');
});

test('manual mobile money orders are marked submitted', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'START',
        'billing_frequency' => 'monthly',
        'domain' => 'none',
        'duration_months' => 3,
        'payment_method' => 'momo_manual',
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000020',
    ]);

    $order = PackageOrder::first();
    expect($order->status)->toBe('submitted')
        ->and($order->payment_method)->toBe('momo_manual')
        ->and($order->total_amount)->toBe(21000 * 3);
});

test('payment verification fails gracefully without keys', function () {
    config()->set('services.flutterwave.secret_key', null);
    $user = User::factory()->create();

    $order = PackageOrder::create([
        'user_id' => $user->id,
        'reference' => 'TTRYY-VERIFY',
        'package' => 'START',
        'billing_frequency' => 'monthly',
        'domain' => 'none',
        'duration_months' => 3,
        'periods' => 3,
        'amount_per_period' => 21000,
        'domain_fee' => 0,
        'total_amount' => 63000,
        'due_today' => 21000,
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000021',
    ]);

    $this->actingAs($user)->postJson('/dashboard/checkout/verify', [
        'transaction_id' => '123456',
        'tx_ref' => 'TTRYY-VERIFY',
    ])->assertUnprocessable();

    expect($order->fresh()->status)->toBe('pending');
});

test('underpaid flutterwave callbacks do not mark the order paid', function () {
    config()->set('services.flutterwave.secret_key', 'test-secret');
    Illuminate\Support\Facades\Http::fake([
        'https://api.flutterwave.com/*' => Illuminate\Support\Facades\Http::response([
            'data' => ['id' => 987654, 'status' => 'successful', 'currency' => 'UGX', 'amount' => 1000],
        ], 200),
    ]);

    $user = User::factory()->create();

    $order = PackageOrder::create([
        'user_id' => $user->id,
        'reference' => 'TTRYY-UNDERPD',
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
        'phone' => '+256 700 000022',
    ]);

    $this->actingAs($user)->postJson('/dashboard/checkout/verify', [
        'transaction_id' => '987654',
        'tx_ref' => 'TTRYY-UNDERPD',
    ])->assertUnprocessable();

    expect($order->fresh()->status)->toBe('pending');
});

test('verified flutterwave payments mark the order paid', function () {
    config()->set('services.flutterwave.secret_key', 'test-secret');
    Illuminate\Support\Facades\Http::fake([
        'https://api.flutterwave.com/*' => Illuminate\Support\Facades\Http::response([
            'data' => ['id' => 987655, 'status' => 'successful', 'currency' => 'UGX', 'amount' => 59000],
        ], 200),
    ]);

    $user = User::factory()->create();

    $order = PackageOrder::create([
        'user_id' => $user->id,
        'reference' => 'TTRYY-PAIDOK1',
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
        'phone' => '+256 700 000023',
    ]);

    $this->actingAs($user)->postJson('/dashboard/checkout/verify', [
        'transaction_id' => '987655',
        'tx_ref' => 'TTRYY-PAIDOK1',
    ])->assertOk()->assertJsonPath('redirect', route('checkout.success', $order));

    $order = $order->fresh();
    expect($order->status)->toBe('paid')
        ->and($order->paid_amount)->toBe(59000)
        ->and($order->payment_method)->toBe('flutterwave');
});

test('owners can download their invoice, strangers cannot', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $order = PackageOrder::create([
        'user_id' => $owner->id,
        'reference' => 'TTRYY-INV001',
        'package' => 'START',
        'billing_frequency' => 'full',
        'domain' => 'none',
        'duration_months' => 12,
        'periods' => 1,
        'amount_per_period' => 250000,
        'domain_fee' => 0,
        'total_amount' => 250000,
        'due_today' => 250000,
        'business_name' => 'Owner Biz',
        'phone' => '+256 700 000024',
    ]);

    $this->actingAs($intruder)->get(route('orders.invoice', $order))->assertForbidden();

    $response = $this->actingAs($owner)->get(route('orders.invoice', $order));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
