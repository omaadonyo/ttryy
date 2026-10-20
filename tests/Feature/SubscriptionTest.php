<?php

use App\Models\HostedWebsite;
use App\Models\SavedContact;
use App\Models\User;
use App\Services\Hosting\HostingSubscription;

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

test('standalone checkout renders for members and guests without the sidebar', function () {
    $this->get(route('checkout'))
        ->assertOk()
        ->assertSee('Create an account or log in', false)
        ->assertDontSee('id="dash-sidebar"', false);

    $this->actingAs(User::factory()->create())->get(route('checkout'))
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

    expect(SavedContact::count())->toBe(2);

    $this->actingAs($user)->get(route('contacts.index'))
        ->assertOk()
        ->assertSee('Nile Builders Ltd', false);
});

test('saved contacts belong to their owner only', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $contact = SavedContact::create([
        'user_id' => $owner->id, 'niche' => 'Construction', 'name' => 'X Ltd',
    ]);

    $this->actingAs($intruder)->delete(route('contacts.destroy', $contact))->assertForbidden();
    $this->actingAs($owner)->delete(route('contacts.destroy', $contact))->assertRedirect();
    expect(SavedContact::count())->toBe(0);
});

test('contacts export downloads a CSV', function () {
    $user = User::factory()->create();
    SavedContact::create([
        'user_id' => $user->id, 'niche' => 'Construction', 'name' => 'Nile Builders Ltd',
        'contact' => 'Brian Mukasa', 'phone' => '+256 772 123 456',
    ]);

    $response = $this->actingAs($user)->get(route('contacts.export'));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('marketing tool lists saved contacts for outreach', function () {
    $user = User::factory()->create();
    SavedContact::create([
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

test('renewal covers as many periods as the amount paid', function () {
    $daily = makeOrder([
        'billing_frequency' => 'daily', 'domain' => 'none', 'duration_months' => 12,
        'periods' => 365, 'amount_per_period' => 700, 'domain_fee' => 0,
        'total_amount' => 255500, 'due_today' => 700, 'paid_amount' => 2100,
    ]);

    expect(HostingSubscription::periodsCovered($daily))->toBe(3);

    $first = makeOrder([
        'billing_frequency' => 'monthly', 'domain' => 'budget',
        'amount_per_period' => 30000, 'domain_fee' => 29000, 'paid_amount' => 59000,
    ]);

    // First payment consumes the one-time domain fee, then one month.
    expect(HostingSubscription::periodsCovered($first))->toBe(1);

    $second = makeOrder([
        'billing_frequency' => 'monthly', 'domain' => 'budget', 'periods_covered' => 1,
        'amount_per_period' => 30000, 'domain_fee' => 29000, 'paid_amount' => 90000,
    ]);

    // Later payments buy whole months with no fee haircut.
    expect(HostingSubscription::periodsCovered($second))->toBe(3);

    $full = makeOrder(['billing_frequency' => 'full', 'periods' => 1, 'paid_amount' => 250000]);

    expect(HostingSubscription::periodsCovered($full))->toBe(1);

    $capped = makeOrder([
        'billing_frequency' => 'weekly', 'periods' => 52, 'periods_covered' => 51,
        'amount_per_period' => 6700, 'domain_fee' => 0, 'paid_amount' => 67000,
    ]);

    // Never extends past the plan even when overpaid.
    expect(HostingSubscription::periodsCovered($capped))->toBe(1);
});

test('recorded payments extend renewal by covered periods and restore overdue sites', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner, [
        'status' => HostedWebsite::STATUS_SUSPENDED,
        'suspension_reason' => 'Overdue payment',
        'next_renewal_at' => now()->subDays(5),
    ]);

    $order = makeOrder([
        'user_id' => $owner->id, 'hosted_website_id' => $site->id,
        'billing_frequency' => 'daily', 'domain' => 'none', 'duration_months' => 12,
        'periods' => 365, 'amount_per_period' => 700, 'domain_fee' => 0,
        'total_amount' => 255500, 'due_today' => 700, 'paid_amount' => 2100,
    ]);

    HostingSubscription::recordPayment($order);

    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_ACTIVE)
        ->and($site->fresh()->suspension_reason)->toBeNull()
        ->and($site->fresh()->next_renewal_at->gt(now()->addDays(2)))->toBeTrue()
        ->and($order->fresh()->periods_covered)->toBe(3);
});

test('admins suspend and restore websites with immediate effect', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    $this->actingAs($admin)->post(route('admin.hosting.websites.suspend', $site), ['reason' => 'Test'])
        ->assertRedirect();
    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_SUSPENDED);

    $this->actingAs($admin)->post(route('admin.hosting.websites.restore', $site))
        ->assertRedirect();
    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_ACTIVE)
        ->and($site->fresh()->suspension_reason)->toBeNull();
});

test('orders page shows paid, remaining and progress', function () {
    $user = User::factory()->create();
    makeOrder(['user_id' => $user->id, 'status' => 'paid', 'paid_amount' => 59000]);

    $total = 389000;
    $left = $total - 59000;
    $pct = (int) min(100, max(0, round(59000 / $total * 100)));

    $this->actingAs($user)->get(route('packages.index'))
        ->assertOk()
        ->assertSee('Paid UGX 59,000', false)
        ->assertSee('UGX '.number_format($left), false)
        ->assertSee('width:'.$pct.'%', false);
});
