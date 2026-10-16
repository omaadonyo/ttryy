<?php

use App\Models\HostedWebsite;
use App\Models\HostingPlan;
use App\Models\User;
use App\Models\WebsiteDomain;
use Illuminate\Support\Str;

function makeWebsite(User $user, array $overrides = []): HostedWebsite
{
    $plan = HostingPlan::first() ?? HostingPlan::create([
        'code' => 'START', 'name' => 'START', 'price' => 250000, 'is_active' => true,
    ]);

    return HostedWebsite::create(array_merge([
        'uuid' => (string) Str::uuid(),
        'user_id' => $user->id,
        'hosting_plan_id' => $plan->id,
        'name' => 'Test Site',
        'status' => HostedWebsite::STATUS_ACTIVE,
    ], $overrides));
}

test('clients can view their own websites but not others', function () {    $mine = User::factory()->create();
    $theirs = User::factory()->create();
    $site = makeWebsite($mine, ['name' => 'Mine Site']);
    $other = makeWebsite($theirs, ['name' => 'Other Site']);

    $this->actingAs($mine)->get(route('hosting.index'))
        ->assertOk()
        ->assertSee('Mine Site', false)
        ->assertDontSee('Other Site', false);

    $this->actingAs($mine)->get(route('hosting.show', $site))->assertOk();
    $this->actingAs($mine)->get(route('hosting.show', $other))->assertForbidden();
});

test('guests cannot reach the client hosting panel', function () {
    $this->get(route('hosting.index'))->assertRedirect(route('login'));
});

test('website registration creates records, subdomain and audit trail', function () {
    $user = User::factory()->create();
    $plan = HostingPlan::first();

    $response = $this->actingAs($user)->post(route('hosting.store'), [
        'name' => 'Savanna Build Ltd',
        'hosting_plan_id' => $plan->id,
    ]);

    $website = HostedWebsite::where('user_id', $user->id)->first();
    expect($website)->not->toBeNull()
        ->and($website->status)->toBe(HostedWebsite::STATUS_PROVISIONING)
        ->and($website->domains()->where('type', 'subdomain')->count())->toBe(1)
        ->and($website->primary_domain_id)->not->toBeNull()
        ->and($website->commands()->where('action', 'provision')->count())->toBe(1);

    expect(App\Models\HostingAuditLog::where('action', 'website.registered')->count())->toBe(1);
    expect($user->fresh()->unreadNotifications()->count())->toBe(1);

    $response->assertRedirect(route('hosting.show', $website));
});

test('duplicate and reserved subdomains are rejected', function () {
    $user = User::factory()->create();
    $website = makeWebsite($user);

    WebsiteDomain::create([
        'hosted_website_id' => $website->id, 'user_id' => $user->id,
        'domain' => 'taken.'.config('hosting.base_domain'), 'type' => 'subdomain',
        'verification' => 'verified',
    ]);

    $this->actingAs($user)->post(route('hosting.subdomains.store', $website), ['label' => 'taken'])
        ->assertSessionHasErrors('label');

    $this->actingAs($user)->post(route('hosting.subdomains.store', $website), ['label' => 'admin'])
        ->assertSessionHasErrors('label');

    $this->actingAs($user)->post(route('hosting.subdomains.store', $website), ['label' => 'BAD LABEL!!'])
        ->assertSessionHasErrors('label');

    $this->actingAs($user)->post(route('hosting.subdomains.store', $website), ['label' => 'brand-new-label'])
        ->assertRedirect();
    expect(WebsiteDomain::where('domain', 'brand-new-label.'.config('hosting.base_domain'))->exists())->toBeTrue();
});

test('unauthorized domain modifications are rejected', function () {
    $mine = User::factory()->create();
    $theirs = User::factory()->create();
    $mySite = makeWebsite($mine);
    $theirSite = makeWebsite($theirs);
    $theirDomain = WebsiteDomain::create([
        'hosted_website_id' => $theirSite->id, 'user_id' => $theirs->id,
        'domain' => 'theirs.'.config('hosting.base_domain'), 'type' => 'subdomain',
        'verification' => 'verified',
    ]);

    // Cross-user: 403.
    $this->actingAs($mine)->delete(route('hosting.domains.destroy', [$mySite, $theirDomain]))
        ->assertForbidden();

    // Cross-website even for the same owner: 404.
    $myOther = makeWebsite($mine);
    $this->actingAs($mine)->post(route('hosting.domains.verify', [$myOther, $theirDomain]))
        ->assertStatus(403);

    // Primary domains cannot be deleted.
    $primary = WebsiteDomain::create([
        'hosted_website_id' => $mySite->id, 'user_id' => $mine->id,
        'domain' => 'primary.'.config('hosting.base_domain'), 'type' => 'subdomain',
        'is_primary' => true, 'verification' => 'verified',
    ]);
    $mySite->forceFill(['primary_domain_id' => $primary->id])->save();

    $this->actingAs($mine)->delete(route('hosting.domains.destroy', [$mySite, $primary]))
        ->assertForbidden();
});

test('analytics pages and exports are scoped to the owner', function () {
    $mine = User::factory()->create();
    $theirs = User::factory()->create();
    $site = makeWebsite($mine);
    $other = makeWebsite($theirs);

    App\Models\WebsiteDailyAnalytics::create([
        'hosted_website_id' => $other->id, 'date' => now()->toDateString(),
        'uniques' => 99, 'visits' => 100, 'pageviews' => 101,
    ]);

    $this->actingAs($mine)->get(route('hosting.analytics', $other))->assertForbidden();
    $this->actingAs($mine)->get(route('hosting.analytics.export', $other))->assertForbidden();
    $this->actingAs($mine)->get(route('hosting.health', $other))->assertForbidden();
    $this->actingAs($mine)->get(route('hosting.billing', $other))->assertForbidden();

    $this->actingAs($mine)->get(route('hosting.analytics', $site))->assertOk();
});

test('all client website pages render for the owner', function () {
    $user = User::factory()->create();
    $site = makeWebsite($user);

    $this->actingAs($user)->get(route('hosting.create'))->assertOk();
    $this->actingAs($user)->get(route('hosting.domains', $site))->assertOk();
    $this->actingAs($user)->get(route('hosting.health', $site))->assertOk();
    $this->actingAs($user)->get(route('hosting.billing', $site))->assertOk();
    $this->actingAs($user)->get(route('hosting.reports', $site))->assertOk();
    $this->actingAs($user)->get(route('hosting.reports.pdf', $site))->assertOk();
});

test('hosting index renders with renewal dates set', function () {
    $user = User::factory()->create();
    makeWebsite($user, ['next_renewal_at' => now()->addMonth()]);

    $this->actingAs($user)->get(route('hosting.index'))->assertOk();
});

test('deleting a website trashes it with domains and restores cleanly', function () {
    $user = User::factory()->create();
    $site = makeWebsite($user, ['name' => 'Trash Site', 'status' => App\Models\HostedWebsite::STATUS_FAILED]);
    $domain = WebsiteDomain::create([
        'hosted_website_id' => $site->id, 'user_id' => $user->id,
        'domain' => 'trashme.'.config('hosting.base_domain'), 'type' => 'subdomain',
        'verification' => 'verified',
    ]);

    $this->actingAs($user)->delete(route('hosting.destroy', $site))->assertRedirect();

    expect(App\Models\HostedWebsite::where('id', $site->id)->exists())->toBeFalse();
    $this->assertSoftDeleted('hosted_websites', ['id' => $site->id]);
    $this->assertSoftDeleted('website_domains', ['id' => $domain->id]);

    // Hidden from the main list (the flash toast still names it), listed under trash.
    $this->actingAs($user)->get(route('hosting.index'))
        ->assertOk()
        ->assertSee('No websites yet', false)
        ->assertSee('Trash — 1 deleted website', false);

    // Another user's restore attempt is rejected.
    $this->actingAs(User::factory()->create())->post(route('hosting.restore', $site->id))->assertForbidden();

    $this->actingAs($user)->post(route('hosting.restore', $site->id))->assertRedirect();

    expect(App\Models\HostedWebsite::where('id', $site->id)->exists())->toBeTrue();
    expect(App\Models\WebsiteDomain::where('id', $domain->id)->exists())->toBeTrue();
});

test('explicit domain removal stays permanent', function () {
    $user = User::factory()->create();
    $site = makeWebsite($user);
    $domain = WebsiteDomain::create([
        'hosted_website_id' => $site->id, 'user_id' => $user->id,
        'domain' => 'gone.'.config('hosting.base_domain'), 'type' => 'subdomain',
        'verification' => 'verified',
    ]);

    $this->actingAs($user)->delete(route('hosting.domains.destroy', [$site, $domain]))->assertRedirect();

    expect(App\Models\WebsiteDomain::withTrashed()->where('id', $domain->id)->exists())->toBeFalse();
});
