<?php

use App\Models\PackageOrder;
use App\Models\User;

function adminUser(): User
{
    return User::factory()->create(['is_admin' => true]);
}

test('guests are redirected and non-admins get 403 on admin pages', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.orders'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.payments'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.users'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.packages'))->assertForbidden();
});

test('admins can open every admin page', function () {
    $this->actingAs(adminUser())->get(route('admin.dashboard'))->assertOk()->assertSee('Administration', false);
    $this->actingAs(adminUser())->get(route('admin.orders'))->assertOk();
    $this->actingAs(adminUser())->get(route('admin.payments'))->assertOk();
    $this->actingAs(adminUser())->get(route('admin.users'))->assertOk();
    $this->actingAs(adminUser())->get(route('admin.packages'))->assertOk()->assertSee('CORPORATE', false);
});

test('admins can mark an order paid', function () {
    $admin = adminUser();
    $order = PackageOrder::create([
        'user_id' => User::factory()->create()->id,
        'reference' => 'TTRYY-ADM001',
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'domain' => 'none',
        'duration_months' => 12,
        'periods' => 12,
        'amount_per_period' => 30000,
        'domain_fee' => 0,
        'total_amount' => 360000,
        'due_today' => 30000,
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000040',
    ]);

    $this->actingAs($admin)->patch(route('admin.orders.paid', $order))->assertRedirect();

    expect($order->fresh()->status)->toBe('paid')
        ->and($order->fresh()->paid_amount)->toBe(30000);
});

test('admins can mark credentials handed over', function () {
    $admin = adminUser();
    $order = PackageOrder::create([
        'user_id' => User::factory()->create()->id,
        'reference' => 'TTRYY-HO1001',
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'domain' => 'none',
        'duration_months' => 12,
        'periods' => 12,
        'amount_per_period' => 30000,
        'domain_fee' => 0,
        'total_amount' => 360000,
        'due_today' => 30000,
        'status' => 'paid',
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000050',
    ]);

    $this->actingAs($admin)->patch(route('admin.orders.handover', $order))->assertRedirect();

    $order = $order->fresh();
    expect($order->credentials_handed_over)->toBeTrue()
        ->and($order->handed_over_at)->not->toBeNull();

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.orders.handover', $order))
        ->assertForbidden();
});

test('non-admins cannot mark orders paid', function () {
    $order = PackageOrder::create([
        'user_id' => User::factory()->create()->id,
        'reference' => 'TTRYY-ADM002',
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
        'phone' => '+256 700 000041',
    ]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.orders.paid', $order))
        ->assertForbidden();

    expect($order->fresh()->status)->toBe('pending');
});

test('admins can grant and revoke admin access but not on themselves', function () {
    $admin = adminUser();
    $user = User::factory()->create();

    $this->actingAs($admin)->patch(route('admin.users.admin', $user))->assertRedirect();
    expect($user->fresh()->is_admin)->toBeTrue();

    $this->actingAs($admin)->patch(route('admin.users.admin', $user))->assertRedirect();
    expect($user->fresh()->is_admin)->toBeFalse();

    $this->actingAs($admin)->patch(route('admin.users.admin', $admin))->assertUnprocessable();
    expect($admin->fresh()->is_admin)->toBeTrue();
});

test('admins can download any invoice', function () {
    $owner = User::factory()->create();

    $order = PackageOrder::create([
        'user_id' => $owner->id,
        'reference' => 'TTRYY-ADM003',
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
        'phone' => '+256 700 000042',
    ]);

    $response = $this->actingAs(adminUser())->get(route('orders.invoice', $order));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
