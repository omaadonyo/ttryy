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
        ->assertSee('sm:grid-cols-2 xl:grid-cols-4', false);

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
