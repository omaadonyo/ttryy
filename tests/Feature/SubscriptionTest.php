<?php

use App\Models\PackageOrder;
use App\Models\User;
use Illuminate\Support\Str;

function makeOrder(array $overrides = []): PackageOrder
{
    $dates = [];
    foreach (['created_at', 'updated_at'] as $key) {
        if (array_key_exists($key, $overrides)) {
            $dates[$key] = $overrides[$key];
            unset($overrides[$key]);
        }
    }

    $order = PackageOrder::create(array_merge([
        'user_id' => User::factory()->create()->id,
        'reference' => 'TTRYY-'.strtoupper(Str::random(6)),
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'domain' => 'budget',
        'duration_months' => 12,
        'periods' => 12,
        'amount_per_period' => 30000,
        'domain_fee' => 29000,
        'total_amount' => 389000,
        'due_today' => 59000,
        'status' => 'paid',
        'business_name' => 'Test Biz',
        'phone' => '+256 700 000000',
    ], $overrides));

    if ($dates !== []) {
        $order->forceFill($dates)->save();
        $order->refresh();
    }

    return $order;
}

test('paid orders report expiry, days left and progress', function () {
    $order = makeOrder(['created_at' => now()->subMonths(3), 'updated_at' => now()->subMonths(3)]);

    expect($order->isActive())->toBeTrue()
        ->and($order->isExpired())->toBeFalse()
        ->and($order->daysLeft())->toBeGreaterThan(200)
        ->and($order->progressPercent())->toBeGreaterThan(10)->toBeLessThan(60)
        ->and($order->expiresAt()->format('Y-m-d'))->toBe(now()->addMonths(9)->format('Y-m-d'));
});

test('expired orders are flagged and show zero days left', function () {
    $order = makeOrder([
        'status' => 'paid',
        'duration_months' => 3,
        'created_at' => now()->subMonths(4),
        'updated_at' => now()->subMonths(4),
    ]);

    expect($order->isActive())->toBeFalse()
        ->and($order->isExpired())->toBeTrue()
        ->and($order->daysLeft())->toBe(0)
        ->and($order->progressPercent())->toBe(100);
});

test('unpaid orders are never active subscriptions', function () {
    $order = makeOrder(['status' => 'pending']);

    expect($order->isActive())->toBeFalse()
        ->and($order->isExpired())->toBeFalse();
});

test('dashboard shows the subscription spotlight', function () {
    $user = User::factory()->create();
    makeOrder(['user_id' => $user->id]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('subscription', false)
        ->assertSee('days left', false);
});

test('guests cannot open the scraper, members can', function () {
    $this->get(route('scraper.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->get(route('scraper.index'))
        ->assertOk()
        ->assertSee('Prospect scraper', false)
        ->assertSee('UGX 10,000', false);
});

test('my packages shows time left on active orders', function () {
    $user = User::factory()->create();
    makeOrder(['user_id' => $user->id, 'created_at' => now()->subMonth(), 'updated_at' => now()->subMonth()]);

    $this->actingAs($user)->get(route('packages.index'))
        ->assertOk()
        ->assertSee('days left', false);
});
