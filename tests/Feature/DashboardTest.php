<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows marketing performance and graphs', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Marketing performance', false)
        ->assertSee('id="dash-mkt-chart"', false);
});

test('dashboard shows kpis, checklist, activity chart and feed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Get set up', false)
        ->assertSee('id="dash-activity-chart"', false)
        ->assertSee('Recent activity', false)
        ->assertSee('vs prior 30 days', false);
});

test('account pages show stats and charts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('packages.index'))
        ->assertOk()
        ->assertSee('My packages', false);

    $this->actingAs($user)->get(route('payments.index'))
        ->assertOk()
        ->assertSee('id="pay-month-chart"', false);

    $this->actingAs($user)->get(route('wallet.index'))
        ->assertOk()
        ->assertSee('id="wallet-flow-chart"', false);

    $this->actingAs($user)->get(route('scraper.index'))
        ->assertOk()
        ->assertSee('Businesses indexed', false);

    $this->actingAs($user)->get(route('prospects.index'))
        ->assertOk()
        ->assertSee('Matching filters', false);

    $this->actingAs($user)->get(route('contacts.index'))
        ->assertOk()
        ->assertSee('With email', false);
});

test('analytics page renders charts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('analytics.index'))
        ->assertOk()
        ->assertSee('id="an-spend"', false)
        ->assertSee('id="an-campaigns"', false)
        ->assertSee('id="an-tokens"', false);
});

test('notifications are tracked and can be opened', function () {    $user = User::factory()->create();
    $user->notify(new App\Notifications\SiteAlert('Hello', 'World', route('dashboard'), 'info'));

    expect($user->unreadNotifications()->count())->toBe(1);

    $n = $user->notifications()->first();

    $this->actingAs($user)->get(route('notifications.open', $n->id))
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);

    $user->notify(new App\Notifications\SiteAlert('Again', 'Body'));
    $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('notification center lists with tabs and palette is present', function () {
    $user = User::factory()->create();
    $user->notify(new App\Notifications\SiteAlert('Center check', 'Body', null, 'order'));

    $this->actingAs($user)->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notification center', false)
        ->assertSee('Center check', false);

    $this->actingAs($user)->get(route('notifications.index', ['tab' => 'unread']))
        ->assertOk()
        ->assertSee('Center check', false);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('id="palette-overlay"', false)
        ->assertSee('View all notifications', false)
        ->assertSee('Notifications', false);
});
