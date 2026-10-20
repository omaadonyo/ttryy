<?php

use App\Models\HostedWebsite;
use App\Models\HostingPlan;
use App\Models\SavedContact;
use App\Models\ScrapedProspect;
use App\Models\User;
use App\Notifications\SiteAlert;
use Illuminate\Support\Str;

function makeSearchWebsite(User $user, string $name): HostedWebsite
{
    $plan = HostingPlan::first() ?? HostingPlan::create([
        'code' => 'START', 'name' => 'START', 'price' => 250000, 'is_active' => true,
    ]);

    return HostedWebsite::create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $user->id,
        'hosting_plan_id' => $plan->id,
        'name' => $name,
        'status' => HostedWebsite::STATUS_ACTIVE,
    ]);
}

test('the palette search returns the users live records', function () {
    $user = User::factory()->create();
    makeOrder(['user_id' => $user->id, 'reference' => 'TTRYY-ZEBRA1', 'business_name' => 'Zebra Traders']);
    $user->notify(new SiteAlert('Zebra campaign won', 'Body', null, 'order'));
    SavedContact::create(['user_id' => $user->id, 'name' => 'Zebra Ltd', 'niche' => 'retail', 'phone' => '0772000111']);
    makeSearchWebsite($user, 'Zebra site');
    ScrapedProspect::create([
        'niche' => 'retail', 'name' => 'Zebra Supermarket', 'category' => 'Grocery',
        'phone' => '0772999888', 'hash' => (string) Str::uuid(),
    ]);

    $this->actingAs($user)
        ->getJson(route('dashboard.search', ['q' => 'zebra']))
        ->assertOk()
        ->assertJsonFragment(['label' => 'TTRYY-ZEBRA1 · GROW'])
        ->assertJsonFragment(['label' => 'Zebra campaign won'])
        ->assertJsonFragment(['label' => 'Zebra site'])
        ->assertJsonFragment(['label' => 'Zebra Ltd'])
        ->assertJsonFragment(['label' => 'Zebra Supermarket']);
});

test('the palette search ignores queries shorter than two characters', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('dashboard.search', ['q' => 'z']))
        ->assertOk()
        ->assertExactJson(['groups' => []]);
});

test('the palette search does not leak other customers records', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    makeOrder(['user_id' => $other->id, 'reference' => 'TTRYY-SECRET1', 'business_name' => 'Secret Co']);

    $this->actingAs($me)
        ->getJson(route('dashboard.search', ['q' => 'secret']))
        ->assertOk()
        ->assertJsonMissing(['label' => 'TTRYY-SECRET1 · GROW']);
});

test('admins can search users from the palette', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    User::factory()->create(['name' => 'Zed Admin Target', 'email' => 'zed.target@example.com']);

    $this->actingAs($admin)
        ->getJson(route('dashboard.search', ['q' => 'zed']))
        ->assertOk()
        ->assertJsonFragment(['label' => 'Zed Admin Target']);
});

test('guests cannot use the palette search', function () {
    $this->getJson(route('dashboard.search', ['q' => 'zebra']))->assertUnauthorized();
});
