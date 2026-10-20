<?php

use App\Models\PackageOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

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
