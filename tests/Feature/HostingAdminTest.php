<?php

use App\Models\HostedWebsite;
use App\Models\HostingAuditLog;
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
        ->assertSee('Platform usage', false)
        ->assertSee('id="admin-users-chart"', false)
        ->assertSee('Tokens sold', false)
        ->assertSee('Campaigns sent', false);

    $this->actingAs($admin)->get(route('admin.hosting.websites'))
        ->assertOk()
        ->assertSee('Estate Site', false);
});

test('suspension takes effect immediately even when the service cannot be reached', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    // No service endpoint configured: command fails honestly, but the
    // website status flips right away so suspension always takes effect.
    $this->actingAs($admin)->post(route('admin.hosting.websites.suspend', $site), [
        'reason' => 'Overdue payment',
    ])->assertRedirect();

    $command = HostingCommand::where('hosted_website_id', $site->id)->first();
    expect($command)->not->toBeNull()
        ->and($command->action)->toBe('suspend')
        ->and($command->state)->toBe(HostingCommand::STATE_FAILED)
        ->and($command->error)->toContain('not configured');

    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_SUSPENDED);
    expect(HostingAuditLog::where('action', 'suspension.requested')->count())->toBe(1);
});

test('failed operations keep the immediate status and can be retried', function () {

    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    $this->actingAs($admin)->post(route('admin.hosting.websites.suspend', $site), ['reason' => 'Test'])
        ->assertRedirect();

    $command = HostingCommand::where('hosted_website_id', $site->id)->first();
    expect($command->state)->toBe(HostingCommand::STATE_FAILED);

    // Retry re-queues; still no endpoint, still failed — but the website
    // stays suspended because the admin action took effect immediately.
    $this->actingAs($admin)->post(route('admin.hosting.operations.retry', $command))->assertRedirect();
    expect($command->fresh()->state)->toBe(HostingCommand::STATE_FAILED);
    expect($site->fresh()->status)->toBe(HostedWebsite::STATUS_SUSPENDED);
});

test('audit log records admin actions with safe metadata', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner);

    $this->actingAs($admin)->post(route('admin.hosting.websites.health', $site))->assertRedirect();

    $log = HostingAuditLog::where('action', 'health.manual_check')->first();
    expect($log)->not->toBeNull()
        ->and($log->actor_type)->toBe('admin')
        ->and($log->operation_id)->not->toBeNull();

    $this->actingAs($admin)->get(route('admin.hosting.audit'))
        ->assertOk()
        ->assertSee('health.manual_check', false);
});

test('admins recover trashed websites or purge them forever', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $site = makeWebsite($owner, ['name' => 'Doomed Site']);
    $site->delete();

    $this->actingAs($admin)->get(route('admin.hosting.websites'))
        ->assertOk()
        ->assertDontSee('Doomed Site', false)
        ->assertSee('Trash (1)', false);

    $this->actingAs($admin)->get(route('admin.hosting.websites', ['show' => 'trash']))
        ->assertOk()
        ->assertSee('Doomed Site', false)
        ->assertSee('Trash — deleted websites', false);

    $this->actingAs($admin)->post(route('admin.hosting.websites.recover', $site->id))
        ->assertRedirect();
    expect(HostedWebsite::where('id', $site->id)->exists())->toBeTrue();

    $site->fresh()->delete();

    $this->actingAs($admin)->delete(route('admin.hosting.websites.purge', $site->id))
        ->assertRedirect();
    expect(HostedWebsite::withTrashed()->where('id', $site->id)->exists())->toBeFalse();
});
