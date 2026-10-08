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

test('new order page renders checkout for members, redirects guests', function () {
    $this->get(route('orders.new'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->get(route('orders.new'))
        ->assertOk()
        ->assertSee('Your domain', false);
});

test('members can save scraped contacts and view them', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('scraper.save'), [
        'niche' => 'Construction',
        'contacts' => [
            ['name' => 'Nile Builders Ltd', 'type' => 'Contractors', 'district' => 'Kampala', 'contact' => 'Brian Mukasa', 'phone' => '+256 772 123 456', 'need' => 'Renovation contracts'],
            ['name' => 'Pearl Estates Ltd', 'type' => 'Developers', 'district' => 'Wakiso', 'contact' => 'Sarah Namono', 'phone' => '+256 701 111 222', 'need' => 'Building projects'],
        ],
    ])->assertOk()->assertJsonPath('saved', 2);

    expect(App\Models\SavedContact::count())->toBe(2);

    $this->actingAs($user)->get(route('contacts.index'))
        ->assertOk()
        ->assertSee('Nile Builders Ltd', false);
});

test('saved contacts belong to their owner only', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $contact = App\Models\SavedContact::create([
        'user_id' => $owner->id, 'niche' => 'Construction', 'name' => 'X Ltd',
    ]);

    $this->actingAs($intruder)->delete(route('contacts.destroy', $contact))->assertForbidden();
    $this->actingAs($owner)->delete(route('contacts.destroy', $contact))->assertRedirect();
    expect(App\Models\SavedContact::count())->toBe(0);
});

test('contacts export downloads a CSV', function () {
    $user = User::factory()->create();
    App\Models\SavedContact::create([
        'user_id' => $user->id, 'niche' => 'Construction', 'name' => 'Nile Builders Ltd',
        'contact' => 'Brian Mukasa', 'phone' => '+256 772 123 456',
    ]);

    $response = $this->actingAs($user)->get(route('contacts.export'));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('marketing tool lists saved contacts for outreach', function () {
    $user = User::factory()->create();
    App\Models\SavedContact::create([
        'user_id' => $user->id, 'niche' => 'Construction', 'name' => 'Nile Builders Ltd',
        'contact' => 'Brian Mukasa', 'phone' => '+256 772 123 456',
    ]);

    $this->get(route('marketing.index'))->assertRedirect(route('login'));

    $this->actingAs($user)->get(route('marketing.index'))
        ->assertOk()
        ->assertSee('Marketing tool', false)
        ->assertSee('Nile Builders Ltd', false);
});

test('payment history shows paid and outstanding totals', function () {
    $user = User::factory()->create();
    makeOrder(['user_id' => $user->id, 'status' => 'paid', 'paid_amount' => 59000]);
    makeOrder(['user_id' => $user->id, 'status' => 'pending', 'reference' => 'TTRYY-HIST02']);

    $this->actingAs($user)->get(route('payments.index'))
        ->assertOk()
        ->assertSee('Payment history', false)
        ->assertSee('59,000', false);
});
