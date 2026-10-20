<?php

use App\Models\LaunchSignup;
use App\Models\PackageOrder;
use App\Models\User;

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('landing header has profile menu, no whatsapp link, and legal pages exist', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('<svg class="w-5 h-5"><use href="#i-chat"/></svg>', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('Choose Your Growth Package', false)
        ->assertSee('sm:grid-cols-2 xl:grid-cols-4', false)
        ->assertSee('For UGX 10,000, get your idea up and running in 5 days.', false)
        ->assertSee('What ttryy adds', false)
        ->assertSee('Typical designer</th>', false)
        ->assertSee('id="edge-rails"', false)
        ->assertSee('edge-hline', false)
        ->assertSee('id="hero3d"', false)
        ->assertSee('three@0.160.0', false)
        ->assertSee('lg:grid-cols-2 lg:items-start', false)
        ->assertDontSee('ttryy: Yes', false)
        ->assertDontSee('Who does what', false);

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('home'))
        ->assertOk()
        ->assertSee('id="landing-menu"', false)
        ->assertSee('Profile settings', false);

    $this->get(route('privacy'))->assertOk()->assertSee('Privacy Policy', false);
    $this->get(route('terms'))->assertOk()->assertSee('Terms of Service', false);
    $this->get(route('sitemap.page'))->assertOk()->assertSee('sitemap.xml', false);

    $xml = $this->get(route('sitemap.xml'))->assertOk();
    expect($xml->headers->get('Content-Type'))->toContain('text/xml');
});

test('scraper results use a uniform fixed height', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('scraper.index'))
        ->assertOk()
        ->assertSee('id="dsc-rows"', false)
        ->assertSee('h-[380px]', false);
});

test('launch campaign page sells the 10k offer with form, countdown and terms', function () {
    $this->get(route('launch'))
        ->assertOk()
        ->assertSee('Launch it for', false)
        ->assertSee('you own the full source code', false)
        ->assertSee('id="launch-pay-now"', false)
        ->assertSee('Apply free, pay later', false)
        ->assertSee('id="countdown"', false)
        ->assertSee('id="apply"', false)
        ->assertSee('Simple terms', false);

    $this->post(route('launch.store'), ['name' => 'x'])->assertSessionHasErrors();

    $this->post(route('launch.store'), [
        'name' => 'Hope Founder',
        'phone' => '+256700000099',
        'email' => 'hope@example.com',
        'idea_name' => 'FarmFresh',
        'niche' => 'Agriculture',
        'idea' => 'An app connecting farmers directly to buyers in Kampala markets.',
        'terms' => '1',
    ])->assertRedirect();

    $signup = LaunchSignup::where('phone', '+256700000099')->first();
    expect($signup)->not->toBeNull()->and($signup->status)->toBe('new');
});

test('admins manage launch signups and create accounts', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $signup = LaunchSignup::create([
        'name' => 'Hope Founder', 'phone' => '+256700000099', 'email' => 'hope.founder@example.com',
        'idea_name' => 'FarmFresh', 'idea' => 'An app connecting farmers directly to buyers in Kampala markets.',
    ]);

    $this->actingAs($admin)->get(route('admin.launch.index'))
        ->assertOk()
        ->assertSee('FarmFresh', false);

    $this->actingAs($admin)->patch(route('admin.launch.status', $signup), [
        'status' => 'contacted', 'admin_note' => 'Called, interested.',
    ])->assertRedirect();
    expect($signup->fresh()->status)->toBe('contacted');

    $this->actingAs($admin)->post(route('admin.launch.create-user', $signup))->assertRedirect();
    $signup->refresh();
    expect($signup->status)->toBe('converted')->and($signup->user_id)->not->toBeNull();

    $user = User::find($signup->user_id);
    expect($user->email)->toContain('hope');

    $order = PackageOrder::where('user_id', $user->id)->first();
    expect($order)->not->toBeNull()
        ->and($order->total_amount)->toBe(10000)
        ->and($order->status)->toBe('pending')
        ->and(str_contains($order->notes ?? '', 'Launch 10K'))->toBeTrue();
});

test('launch account creation carries an already-paid fee onto the order', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $signup = LaunchSignup::create([
        'name' => 'Paid Founder', 'phone' => '+256700000098', 'email' => 'paid.founder@example.com',
        'idea_name' => 'PaidFresh', 'idea' => 'An app connecting farmers directly to buyers in Kampala markets.',
        'payment_status' => 'paid', 'paid_amount' => 10000, 'gateway_tx_id' => 'iotec-tx-1',
    ]);

    $this->actingAs($admin)->post(route('admin.launch.create-user', $signup))->assertRedirect();

    $order = PackageOrder::where('user_id', $signup->fresh()->user_id)->first();
    expect($order->status)->toBe('paid')
        ->and($order->paid_amount)->toBe(10000)
        ->and($order->payment_method)->toBe('iotec');
});

test('admins credit cash and mobile money against orders', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->create();
    $order = makeOrder(['user_id' => $owner->id, 'status' => 'submitted', 'paid_amount' => 0]);

    $this->actingAs($admin)->patch(route('admin.orders.payment', $order), [
        'amount' => 9000, 'method' => 'cash',
    ])->assertRedirect();

    expect($order->fresh()->paid_amount)->toBe(9000)
        ->and($order->fresh()->status)->toBe('submitted')
        ->and($order->fresh()->payment_method)->toBe('cash');

    $this->actingAs($admin)->patch(route('admin.orders.payment', $order), [
        'amount' => 380000, 'method' => 'momo_manual',
    ])->assertRedirect();

    expect($order->fresh()->paid_amount)->toBe(389000)
        ->and($order->fresh()->status)->toBe('paid');

    $this->actingAs($admin)->patch(route('admin.orders.payment', $order), [
        'amount' => 100, 'method' => 'cash',
    ])->assertStatus(422);
});
