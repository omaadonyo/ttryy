<?php

use App\Models\OrderPayment;
use App\Models\User;

test('customers can pay an order from their wallet', function () {
    $user = User::factory()->create();
    $user->forceFill(['balance_ugx' => 50000])->save();

    $order = makeOrder([
        'user_id' => $user->id,
        'status' => 'pending',
        'paid_amount' => 0,
        'domain_fee' => 0,
        'total_amount' => 250000,
        'amount_per_period' => 83334,
        'periods' => 3,
        'due_today' => 83334,
    ]);

    $this->actingAs($user)->postJson(route('orders.pay', $order), [
        'amount' => 30000,
        'method' => 'wallet',
    ])->assertOk()->assertJsonPath('status', 'paid');

    expect($order->fresh()->paid_amount)->toBe(30000)
        ->and($order->fresh()->status)->toBe('submitted')
        ->and($user->fresh()->balance_ugx)->toBe(20000)
        ->and(OrderPayment::where('package_order_id', $order->id)->where('status', 'paid')->count())->toBe(1);
});

test('paying more than the wallet holds is rejected', function () {
    $user = User::factory()->create();
    $order = makeOrder([
        'user_id' => $user->id,
        'status' => 'pending',
        'paid_amount' => 0,
        'domain_fee' => 0,
        'total_amount' => 250000,
    ]);

    $this->actingAs($user)->postJson(route('orders.pay', $order), [
        'amount' => 5000,
        'method' => 'wallet',
    ])->assertUnprocessable();

    expect($order->fresh()->paid_amount)->toBe(0)
        ->and($user->fresh()->balance_ugx)->toBe(0);
});

test('customers cannot pay another persons order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $other->forceFill(['balance_ugx' => 100000])->save();

    $order = makeOrder(['user_id' => $owner->id, 'paid_amount' => 0, 'total_amount' => 250000]);

    $this->actingAs($other)->postJson(route('orders.pay', $order), [
        'amount' => 10000,
        'method' => 'wallet',
    ])->assertForbidden();
});

test('fully paid orders reject further payments', function () {
    $user = User::factory()->create();
    $user->forceFill(['balance_ugx' => 100000])->save();

    $order = makeOrder([
        'user_id' => $user->id,
        'status' => 'paid',
        'paid_amount' => 389000,
        'total_amount' => 389000,
    ]);

    $this->actingAs($user)->postJson(route('orders.pay', $order), [
        'amount' => 10000,
        'method' => 'wallet',
    ])->assertUnprocessable();

    expect($user->fresh()->balance_ugx)->toBe(100000);
});

test('mobile money is politely unavailable when ioTec is not configured', function () {
    $user = User::factory()->create();
    $order = makeOrder(['user_id' => $user->id, 'paid_amount' => 0, 'total_amount' => 250000]);

    $this->actingAs($user)->postJson(route('orders.pay', $order), [
        'amount' => 10000,
        'method' => 'momo',
        'phone' => '0772123456',
    ])->assertUnprocessable();
});

test('my packages page exposes the pay panel and wallet balance', function () {
    $user = User::factory()->create();
    $user->forceFill(['balance_ugx' => 75000])->save();

    makeOrder(['user_id' => $user->id, 'status' => 'pending', 'paid_amount' => 0, 'total_amount' => 250000]);

    $this->actingAs($user)->get(route('packages.index'))
        ->assertOk()
        ->assertSee('Pay this order', false)
        ->assertSee('UGX 75,000', false);
});
