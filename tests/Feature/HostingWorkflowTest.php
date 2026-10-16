<?php

use App\Models\HostedWebsite;
use App\Models\HostingCommand;
use App\Models\User;
use App\Services\Hosting\AnalyticsRecorder;
use App\Services\Hosting\ServiceAuthenticator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('provisioning succeeds on service acknowledgment', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner, ['status' => HostedWebsite::STATUS_PROVISIONING]);
    $site->forceFill(['service_url' => 'https://site-one.example'])->save();
    [$credential, $secret] = ServiceAuthenticator::issueCredential($site);

    Http::fake([
        'site-one.example/*' => Http::response([
            'ok' => true, 'operation_id' => 'x',
            'service_state' => 'active', 'service_version' => '1.0.0',
        ], 200),
    ]);

    $command = App\Services\Hosting\ServiceCommander::build($site, HostingCommand::ACTION_PROVISION);
    $command = App\Services\Hosting\ServiceCommander::deliver($command);

    expect($command->state)->toBe(HostingCommand::STATE_SUCCEEDED);
    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_ACTIVE);
    expect($owner->fresh()->unreadNotifications()->count())->toBe(1);
});

test('provisioning failure keeps the site out of active state', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner, ['status' => HostedWebsite::STATUS_PROVISIONING]);
    $site->forceFill(['service_url' => 'https://site-two.example'])->save();
    ServiceAuthenticator::issueCredential($site);

    Http::fake(['site-two.example/*' => Http::response('Server error', 500)]);

    $command = App\Services\Hosting\ServiceCommander::build($site, HostingCommand::ACTION_PROVISION);

    for ($i = 0; $i < 4; $i++) {
        $command = App\Services\Hosting\ServiceCommander::deliver($command->fresh());
    }

    expect($command->state)->toBe(HostingCommand::STATE_FAILED);
    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_PROVISIONING);
});

test('service acknowledgment flips status on delivery', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner, ['status' => HostedWebsite::STATUS_ACTIVE]);
    $site->forceFill(['service_url' => 'https://site-three.example'])->save();
    ServiceAuthenticator::issueCredential($site);

    Http::fake([
        'site-three.example/*' => Http::response([
            'ok' => true, 'operation_id' => 'x',
            'service_state' => 'suspended', 'service_version' => '1.0.0',
        ], 200),
    ]);

    $suspend = App\Services\Hosting\ServiceCommander::deliver(
        App\Services\Hosting\ServiceCommander::build($site, HostingCommand::ACTION_SUSPEND, 'admin', null, ['reason' => 'Overdue'])
    );
    expect($suspend->state)->toBe(HostingCommand::STATE_SUCCEEDED);
    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_SUSPENDED);

    Http::fake([
        'site-three.example/*' => Http::response([
            'ok' => true, 'operation_id' => 'y',
            'service_state' => 'active', 'service_version' => '1.0.0',
        ], 200),
    ]);

    $restore = App\Services\Hosting\ServiceCommander::deliver(
        App\Services\Hosting\ServiceCommander::build($site->fresh(), HostingCommand::ACTION_UNSUSPEND)
    );
    expect($restore->state)->toBe(HostingCommand::STATE_SUCCEEDED);
    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_ACTIVE);
});

test('health command records checks and flags incidents', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    Http::fake(['*' => Http::response('down', 500)]);

    $this->artisan('hosting:health-check')->assertOk();

    $check = $site->healthChecks()->latest('checked_at')->first();
    // No primary domain: recorded as unknown, honestly.
    expect($check)->not->toBeNull();
    expect($site->fresh()->health_status)->toBe(HostedWebsite::HEALTH_UNKNOWN);
});

test('analytics beacon records events and aggregates honestly', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    $this->get(route('hosting.beacon', $site->uuid).'?p=/pricing&utm_source=google')
        ->assertOk();
    $this->get(route('hosting.beacon', $site->uuid).'?p=/pricing&utm_source=google')
        ->assertOk();

    expect(App\Models\WebsiteAnalyticsEvent::where('hosted_website_id', $site->id)->count())->toBe(2);

    $day = AnalyticsRecorder::aggregateDay($site, now()->toDateString());
    expect($day->pageviews)->toBe(2)
        ->and($day->uniques)->toBe(1)
        ->and($day->visits)->toBe(1);

    $this->actingAs($owner)->get(route('hosting.analytics.export', $site))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=analytics-'.$site->uuid.'-30d.csv');
});

test('paid orders notify website owners', function () {    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    $order = App\Models\PackageOrder::create([
        'user_id' => $owner->id, 'hosted_website_id' => $site->id,
        'reference' => 'TTRYY-WF01', 'package' => 'GROW', 'billing_frequency' => 'monthly',
        'domain' => 'none', 'duration_months' => 12, 'periods' => 12,
        'amount_per_period' => 30000, 'domain_fee' => 0, 'total_amount' => 360000,
        'due_today' => 30000, 'currency' => 'UGX', 'status' => 'submitted',
        'payment_method' => 'momo_manual', 'business_name' => 'WF Biz', 'phone' => '+256700000001',
    ]);

    $this->actingAs($admin)->patch(route('admin.orders.paid', $order))->assertRedirect();

    $note = $owner->fresh()->unreadNotifications->firstWhere(fn ($n) => str_contains($n->data['title'] ?? '', 'Payment confirmed'));
    expect($note)->not->toBeNull();
});

test('verified payments extend renewal on the payment rhythm', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner, ['next_renewal_at' => now()->subDay()]);

    $order = App\Models\PackageOrder::create([
        'user_id' => $owner->id, 'hosted_website_id' => $site->id,
        'reference' => 'TTRYY-RH01', 'package' => 'GROW', 'billing_frequency' => 'weekly',
        'domain' => 'none', 'duration_months' => 12, 'periods' => 52,
        'amount_per_period' => 6700, 'domain_fee' => 0, 'total_amount' => 348400,
        'due_today' => 6700, 'currency' => 'UGX', 'status' => 'pending',
        'payment_method' => 'flutterwave', 'business_name' => 'RH Biz', 'phone' => '+256700000041',
    ]);

    App\Services\Hosting\HostingSubscription::recordPayment($order);

    $renewal = $site->fresh()->next_renewal_at;
    expect($renewal)->not->toBeNull()
        ->and($renewal->gt(now()->addDays(6)))->toBeTrue();
});

test('overdue sweep suspends immediately and never duplicates', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner, [
        'status' => App\Models\HostedWebsite::STATUS_ACTIVE,
        'next_renewal_at' => now()->subDays(3),
    ]);

    App\Models\PackageOrder::create([
        'user_id' => $owner->id, 'hosted_website_id' => $site->id,
        'reference' => 'TTRYY-OD01', 'package' => 'START', 'billing_frequency' => 'monthly',
        'domain' => 'none', 'duration_months' => 12, 'periods' => 12,
        'amount_per_period' => 21000, 'domain_fee' => 0, 'total_amount' => 252000,
        'due_today' => 21000, 'currency' => 'UGX', 'status' => 'pending',
        'payment_method' => 'flutterwave', 'business_name' => 'OD Biz', 'phone' => '+256700000042',
    ]);

    expect(App\Services\Hosting\HostingSubscription::suspendOverdue())->toBe(1);
    expect($site->fresh()->status)->toBe(App\Models\HostedWebsite::STATUS_SUSPENDED)
        ->and($site->fresh()->suspension_reason)->toBe('Overdue payment');
    expect(App\Services\Hosting\HostingSubscription::suspendOverdue())->toBe(0);
    expect(App\Models\HostingCommand::where('hosted_website_id', $site->id)->where('action', 'suspend')->count())->toBe(1);
});

test('admins manage domains and link orders to websites', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    $this->actingAs($admin)->post(route('admin.hosting.domains.subdomain', $site), ['label' => 'admin-made'])
        ->assertRedirect();
    expect(App\Models\WebsiteDomain::where('domain', 'admin-made.'.config('hosting.base_domain'))->exists())->toBeTrue();

    $domain = $site->domains()->first();
    $this->actingAs($admin)->post(route('admin.hosting.domains.primary', [$site, $domain->id]))
        ->assertRedirect();
    expect($domain->fresh()->is_primary)->toBeTrue();

    $order = App\Models\PackageOrder::create([
        'user_id' => $owner->id, 'reference' => 'TTRYY-LK01', 'package' => 'GROW',
        'billing_frequency' => 'monthly', 'domain' => 'none', 'duration_months' => 12,
        'periods' => 12, 'amount_per_period' => 30000, 'domain_fee' => 0,
        'total_amount' => 360000, 'due_today' => 30000, 'currency' => 'UGX',
        'status' => 'pending', 'payment_method' => 'flutterwave',
        'business_name' => 'LK Biz', 'phone' => '+256700000043',
    ]);

    $this->actingAs($admin)->post(route('admin.hosting.websites.link-order', $site), ['order_id' => $order->id])
        ->assertRedirect();
    expect($order->fresh()->hosted_website_id)->toBe($site->id);
});

test('subdomain provisioner is honest without infrastructure', function () {
    $owner = User::factory()->create();
    $site = makeWebsite($owner);
    $domain = App\Models\WebsiteDomain::create([
        'hosted_website_id' => $site->id, 'user_id' => $owner->id,
        'domain' => 'probe-xyz.'.config('hosting.base_domain'), 'type' => 'subdomain',
        'verification' => 'verified',
    ]);

    $result = App\Services\Hosting\SubdomainProvisioner::provision($domain);

    expect($result['ok'])->toBeTrue()->and($result['mode'])->toBe('wildcard');
});
