<?php

use App\Models\PackageOrder;
use App\Models\User;

test('guests are redirected to login when visiting checkout', function () {
    $this->get('/checkout?package=GROW')->assertRedirect('/login');
});

test('checkout page shows the preselected package', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/checkout?package=BUSINESS')
        ->assertOk()
        ->assertSee('UGX 650,000', false);
});

test('authenticated users can place a monthly package order', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/checkout', [
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'duration_months' => 12,
        'business_name' => 'Savanna Build Ltd',
        'phone' => '+256 700 000000',
        'niche' => 'Construction',
    ]);

    $order = PackageOrder::first();
    expect($order)->not->toBeNull()
        ->and($order->user_id)->toBe($user->id)
        ->and($order->total_amount)->toBe(30000 * 12)
        ->and($order->periods)->toBe(12)
        ->and($order->status)->toBe('pending');

    $response->assertRedirect(route('checkout.success', $order));
});

test('order totals are computed server-side, not trusted from the client', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/checkout', [
        'package' => 'START',
        'billing_frequency' => 'weekly',
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

    $this->actingAs($user)->post('/checkout', [
        'package' => 'BUSINESS',
        'billing_frequency' => 'full',
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000002',
    ]);

    $order = PackageOrder::first();
    expect($order->total_amount)->toBe(650000)
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

    $this->actingAs($user)->post('/checkout', [
        'package' => 'START',
        'billing_frequency' => 'full',
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000004',
    ]);

    $this->actingAs($user)->get(route('packages.index'))
        ->assertOk()
        ->assertSee('START')
        ->assertSee('Test Biz');
});
