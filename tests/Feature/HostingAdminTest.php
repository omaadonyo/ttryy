<?php

use App\Models\HostedWebsite;
use App\Models\HostingCommand;
use App\Models\User;

test('guests and non-admins cannot access the admin hosting panel', function () {
    $this->get(route('admin.hosting.dashboard'))->assertRedirect(route('login'));

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('admin.hosting.dashboard'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.hosting.websites'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.hosting.audit'))->assertForbidden();
});

test('admins can view the whole hosting estate', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    makeWebsite($owner, ['name' => 'Estate Site']);

    $this->actingAs($admin)->get(route('admin.hosting.dashboard'))
        ->assertOk()
        ->assertSee('Platform usage', false);

    $this->actingAs($admin)->get(route('admin.hosting.websites'))
        ->assertOk()
        ->assertSee('Estate Site', false);
});

test('suspension is recorded but status waits for service acknowledgment', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    // No service endpoint configured: command fails honestly, status untouched.
    $this->actingAs($admin)->post(route('admin.hosting.websites.suspend', $site), [
        'reason' => 'Overdue payment',
    ])->assertRedirect();

    $command = HostingCommand::where('hosted_website_id', $site->id)->first();
    expect($command)->not->toBeNull()
        ->and($command->action)->toBe('suspend')
        ->and($command->state)->toBe(HostingCommand::STATE_FAILED)
        ->and($command->error)->toContain('not configured');

    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_ACTIVE);
    expect(App\Models\HostingAuditLog::where('action', 'suspension.requested')->count())->toBe(1);
});

test('failed operations do not change confirmed status and can be retried', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    $this->actingAs($admin)->post(route('admin.hosting.websites.suspend', $site), ['reason' => 'Test'])
        ->assertRedirect();

    $command = HostingCommand::where('hosted_website_id', $site->id)->first();
    expect($command->state)->toBe(HostingCommand::STATE_FAILED);

    // Retry re-queues; still no endpoint, still failed, status still active.
    $this->actingAs($admin)->post(route('admin.hosting.operations.retry', $command))->assertRedirect();
    expect($command->fresh()->state)->toBe(HostingCommand::STATE_FAILED);
    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_ACTIVE);
});

test('audit log records admin actions with safe metadata', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    $this->actingAs($admin)->post(route('admin.hosting.websites.health', $site))->assertRedirect();

    $log = App\Models\HostingAuditLog::where('action', 'health.manual_check')->first();
    expect($log)->not->toBeNull()
        ->and($log->actor_type)->toBe('admin')
        ->and($log->operation_id)->not->toBeNull();

    $this->actingAs($admin)->get(route('admin.hosting.audit'))
        ->assertOk()
        ->assertSee('health.manual_check', false);
});
