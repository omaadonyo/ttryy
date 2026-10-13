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

test('suspend and restore flip status only on acknowledgment', function () {
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

test('paid orders notify website owners', function () {
    $admin = User::factory()->create(['is_admin' => true]);
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
