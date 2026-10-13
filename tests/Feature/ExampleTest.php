<?php

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
        ->assertDontSee('Who does what', false);

    $user = App\Models\User::factory()->create();
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
    $user = App\Models\User::factory()->create();

    $this->actingAs($user)->get(route('scraper.index'))
        ->assertOk()
        ->assertSee('id="dsc-rows"', false)
        ->assertSee('h-[380px]', false);
});

test('launch campaign page sells the 10k offer with form, countdown and terms', function () {
    $this->get(route('launch'))
        ->assertOk()
        ->assertSee('Launch it for', false)
        ->assertSee('768665', false)
        ->assertSee('Flutterwave', false)
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

    $signup = App\Models\LaunchSignup::where('phone', '+256700000099')->first();
    expect($signup)->not->toBeNull()->and($signup->status)->toBe('new');
});

test('admins manage launch signups and create accounts', function () {
    $admin = App\Models\User::factory()->create(['is_admin' => true]);
    $signup = App\Models\LaunchSignup::create([
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

    $user = App\Models\User::find($signup->user_id);
    expect($user->email)->toContain('hope');

    $plain = App\Models\User::where('email', $user->email)->first();
    expect($plain)->not->toBeNull();
});
