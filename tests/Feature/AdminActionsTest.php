<?php

use App\Models\HostedWebsite;
use App\Models\HostingPlan;
use App\Models\PackageOrder;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\WebsiteDomain;
use Illuminate\Support\Str;

function adminActionsAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function adminActionsWebsite(User $user, array $overrides = []): HostedWebsite
{
    $plan = HostingPlan::first() ?? HostingPlan::create([
        'code' => 'START', 'name' => 'START', 'price' => 250000, 'is_active' => true,
    ]);

    return HostedWebsite::create(array_merge([
        'uuid' => (string) Str::uuid(),
        'user_id' => $user->id,
        'hosting_plan_id' => $plan->id,
        'name' => 'Managed Site',
        'status' => HostedWebsite::STATUS_ACTIVE,
    ], $overrides));
}

function adminActionsOrder(User $user, array $overrides = []): PackageOrder
{
    return PackageOrder::create(array_merge([
        'user_id' => $user->id,
        'reference' => 'TTRYY-'.strtoupper(Str::random(6)),
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'domain' => 'none',
        'duration_months' => 12,
        'periods' => 12,
        'amount_per_period' => 30000,
        'domain_fee' => 0,
        'total_amount' => 360000,
        'due_today' => 30000,
        'status' => 'pending',
        'business_name' => 'Action Biz',
        'phone' => '+256 700 111222',
    ], $overrides));
}

test('a suspended account is signed out on its very next request', function () {
    $user = User::factory()->create();
    $user->forceFill(['suspended_at' => now()])->save();

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('admins can suspend and reactivate a user', function () {
    $admin = adminActionsAdmin();
    $user = User::factory()->create();

    $this->actingAs($admin)->patch(route('admin.users.suspend', $user))->assertRedirect();
    expect($user->fresh()->suspended_at)->not->toBeNull();

    $this->actingAs($user->fresh())->get(route('dashboard'))->assertRedirect(route('login'));

    $this->actingAs($admin)->patch(route('admin.users.activate', $user))->assertRedirect();
    expect($user->fresh()->suspended_at)->toBeNull();

    $this->actingAs($user->fresh())->get(route('dashboard'))->assertOk();
});

test('admins cannot suspend or delete themselves or other admins', function () {
    $admin = adminActionsAdmin();

    $this->actingAs($admin)->patch(route('admin.users.suspend', $admin))->assertStatus(422);
    $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertStatus(422);

    $other = adminActionsAdmin();

    $this->actingAs($admin)->patch(route('admin.users.suspend', $other))->assertStatus(422);
    $this->actingAs($admin)->delete(route('admin.users.destroy', $other))->assertStatus(422);
});

test('non-admins cannot touch user or order administration', function () {
    $user = User::factory()->create();
    $victim = User::factory()->create();
    $order = adminActionsOrder($victim);

    $this->actingAs($user)->patch(route('admin.users.suspend', $victim))->assertForbidden();
    $this->actingAs($user)->delete(route('admin.users.destroy', $victim))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.orders.refund', $order))->assertForbidden();
    $this->actingAs($user)->delete(route('admin.orders.destroy', $order))->assertForbidden();
});

test('admins can trash, restore and purge a user', function () {
    $admin = adminActionsAdmin();
    $user = User::factory()->create(['email' => 'trashme-action@example.com']);

    $this->actingAs($admin)->delete(route('admin.users.destroy', $user))->assertRedirect();
    $this->assertSoftDeleted('users', ['id' => $user->id]);

    $this->actingAs($admin)->get(route('admin.users', ['show' => 'trash']))
        ->assertOk()
        ->assertSee('trashme-action@example.com', false);

    $this->actingAs($admin)->post(route('admin.users.restore', $user->id))->assertRedirect();
    expect(User::where('id', $user->id)->exists())->toBeTrue();
    expect($user->fresh()->suspended_at)->toBeNull();

    $user->fresh()->delete();
    $this->actingAs($admin)->delete(route('admin.users.purge', $user->id))->assertRedirect();
    expect(User::withTrashed()->where('id', $user->id)->exists())->toBeFalse();
});

test('suspended filter lists only suspended users', function () {
    $admin = adminActionsAdmin();
    User::factory()->create(['email' => 'active-user@example.com']);
    User::factory()->create(['email' => 'blocked-user@example.com', 'suspended_at' => now()]);

    $this->actingAs($admin)->get(route('admin.users', ['show' => 'suspended']))
        ->assertOk()
        ->assertSee('blocked-user@example.com', false)
        ->assertDontSee('active-user@example.com', false);
});

test('admins can refund a paid order', function () {
    $admin = adminActionsAdmin();
    $owner = User::factory()->create();
    $order = adminActionsOrder($owner, ['status' => 'paid', 'paid_amount' => 30000, 'paid_at' => now()]);

    $this->actingAs($admin)->patch(route('admin.orders.refund', $order))->assertRedirect();

    $order = $order->fresh();
    expect($order->status)->toBe('refunded')
        ->and($order->refund_amount)->toBe(30000)
        ->and($order->refunded_at)->not->toBeNull();

    $this->actingAs($admin)->patch(route('admin.orders.refund', $order))->assertStatus(422);
});

test('orders with no payment cannot be refunded', function () {
    $admin = adminActionsAdmin();
    $order = adminActionsOrder(User::factory()->create());

    $this->actingAs($admin)->patch(route('admin.orders.refund', $order))->assertStatus(422);
    expect($order->fresh()->status)->toBe('pending');
});

test('admins can delete an order', function () {
    $admin = adminActionsAdmin();
    $order = adminActionsOrder(User::factory()->create());

    $this->actingAs($admin)->delete(route('admin.orders.destroy', $order))->assertRedirect();

    expect(PackageOrder::where('id', $order->id)->exists())->toBeFalse();
});

test('admins can move a website to trash and recover it', function () {
    $admin = adminActionsAdmin();
    $owner = User::factory()->create();
    $site = adminActionsWebsite($owner, ['name' => 'Admin Trash Site']);

    $this->actingAs($admin)->delete(route('admin.hosting.websites.destroy', $site))->assertRedirect();
    $this->assertSoftDeleted('hosted_websites', ['id' => $site->id]);

    $this->actingAs($admin)->get(route('admin.hosting.websites', ['show' => 'trash']))
        ->assertOk()
        ->assertSee('Admin Trash Site', false);

    $this->actingAs($admin)->post(route('admin.hosting.websites.recover', $site->id))->assertRedirect();
    expect(HostedWebsite::where('id', $site->id)->exists())->toBeTrue();
});

test('admins can delete a domain from the domain list', function () {
    $admin = adminActionsAdmin();
    $owner = User::factory()->create();
    $site = adminActionsWebsite($owner);
    $domain = WebsiteDomain::create([
        'hosted_website_id' => $site->id, 'user_id' => $owner->id,
        'domain' => 'bye.'.config('hosting.base_domain'), 'type' => 'subdomain',
        'verification' => 'verified',
    ]);

    $this->actingAs($admin)->get(route('admin.hosting.domains'))
        ->assertOk()
        ->assertSee('bye.'.config('hosting.base_domain'), false);

    $this->actingAs($admin)->delete(route('admin.hosting.domains.destroy', [$site, $domain]))->assertRedirect();
    expect(WebsiteDomain::withTrashed()->where('id', $domain->id)->exists())->toBeFalse();
});

test('the client hosting list offers a delete action', function () {
    $user = User::factory()->create();
    adminActionsWebsite($user, ['name' => 'Listed Site']);

    $this->actingAs($user)->get(route('hosting.index'))
        ->assertOk()
        ->assertSee('Listed Site', false)
        ->assertSee('Delete', false);
});

test('admins can credit tokens to a customer wallet', function () {
    $admin = adminActionsAdmin();
    $user = User::factory()->create(['token_balance' => 5]);

    $this->actingAs($admin)->patch(route('admin.users.tokens', $user), [
        'amount' => 250,
        'reason' => 'Goodwill credit',
    ])->assertRedirect();

    expect($user->fresh()->token_balance)->toBe(255);
    expect(TokenTransaction::where('user_id', $user->id)
        ->where('type', 'credit')->where('amount', 250)->exists())->toBeTrue();
});

test('non-admins cannot credit tokens', function () {
    $user = User::factory()->create(['token_balance' => 5]);
    $victim = User::factory()->create();

    $this->actingAs($user)->patch(route('admin.users.tokens', $victim), ['amount' => 100])->assertForbidden();

    expect($victim->fresh()->token_balance)->toBe(0);
});
