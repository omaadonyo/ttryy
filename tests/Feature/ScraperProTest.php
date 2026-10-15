<?php

use App\Models\SavedContact;
use App\Models\ScrapedProspect;
use App\Models\TokenTransaction;
use App\Models\User;
use App\Models\WhatsappGroup;
use App\Services\Wallet;
use Illuminate\Support\Facades\Http;

function proSampleHtml(): string
{
    return <<<'HTML'
    <html><body>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-4 shadow-lg">
        <div class="pt-2 flex items-center gap-2 flex-wrap">
          <a title="Construction" href="/business-category/construction"><span>Construction</span></a>
          <span class="inline-flex">Verified</span>
        </div>
        <h2 class="text-2xl font-bold">Nile Builders Limited</h2>
        <address class="not-italic">Plot 12, Jinja Road<div>Kampala, Kampala</div></address>
        <a title="Call Nile" href="tel:+256772123456"><span>Telephone</span></a>
        <a title="View Listing" href="/business-category/construction/business-details/1-nile"><span>View Listing</span></a>
      </div>
    </body></html>
    HTML;
}

function proUser(int $tokens = 100): User
{
    $user = User::factory()->create();

    if ($tokens > 0) {
        Wallet::credit($user, $tokens, 'Test top-up');
    }

    return $user->fresh();
}

test('fresh directory pulls cost tokens, cached repeats are free', function () {
    Http::fake(['www.yellowpages.co.ug/*' => Http::response(proSampleHtml(), 200)]);

    $user = proUser(100);

    $this->actingAs($user)->postJson('/dashboard/scraper/search', [
        'niche' => 'Construction', 'keyword' => 'construction',
    ])->assertOk()->assertJsonPath('cached', false);

    expect($user->fresh()->token_balance)->toBe(95)
        ->and(TokenTransaction::where('user_id', $user->id)->where('amount', 5)->exists())->toBeTrue();

    // Warm the index to the cache threshold — repeats are served free.
    for ($i = 0; $i < 9; $i++) {
        ScrapedProspect::create([
            'niche' => 'Construction', 'name' => "Cached Builder {$i}", 'category' => 'Construction',
            'phone' => "+2567720001{$i}", 'source' => 'yellow-pages',
            'hash' => md5("cached-{$i}"), 'fetched_at' => now(),
        ]);
    }

    $this->actingAs($user)->postJson('/dashboard/scraper/search', [
        'niche' => 'Construction', 'keyword' => 'construction',
    ])->assertOk()->assertJsonPath('cached', true);

    expect($user->fresh()->token_balance)->toBe(95);
});

test('broke users get a top-up prompt instead of a search', function () {
    $user = proUser(0);

    $this->actingAs($user)->postJson('/dashboard/scraper/search', [
        'niche' => 'Construction', 'keyword' => 'construction',
    ])->assertStatus(402)->assertJsonPath('topup_url', route('wallet.index'));

    expect($user->fresh()->token_balance)->toBe(0);
});

test('empty searches are free and never charge tokens', function () {
    Http::fake(['www.yellowpages.co.ug/*' => Http::response('boom', 500)]);

    $user = proUser(100);

    $this->actingAs($user)->postJson('/dashboard/scraper/search', [
        'niche' => ' imaging', 'keyword' => 'zzz-no-such-niche-zzz',
    ])->assertOk()->assertJsonPath('total_found', 0);

    expect($user->fresh()->token_balance)->toBe(100);
});

test('scraped records expose their directory source', function () {
    Http::fake(['www.yellowpages.co.ug/*' => Http::response(proSampleHtml(), 200)]);

    $user = proUser(100);

    $res = $this->actingAs($user)->postJson('/dashboard/scraper/search', [
        'niche' => 'Construction', 'keyword' => 'construction',
    ])->assertOk();

    $record = $res->json('records.0');
    expect($record['source'])->toBe('yellow-pages')
        ->and($record['source_label'])->toBe('Yellow Pages Uganda')
        ->and($record['source_url'])->toStartWith('https://www.yellowpages.co.ug');

    $stored = ScrapedProspect::first();
    expect($stored->detail_url)->toStartWith('https://www.yellowpages.co.ug')
        ->and($stored->sourceLink())->toBe($stored->detail_url);
});

test('prospects page tags fresh records new and links sources', function () {
    $user = proUser();

    ScrapedProspect::create([
        'niche' => 'Construction', 'name' => 'Fresh Builders Ltd', 'category' => 'Construction',
        'phone' => '+256772000001', 'source' => 'yellow-pages',
        'source_url' => 'https://www.yellowpages.co.ug/search-results/construction',
        'detail_url' => 'https://www.yellowpages.co.ug/business-details/1-fresh',
        'hash' => md5('fresh'), 'fetched_at' => now(),
    ]);
    ScrapedProspect::create([
        'niche' => 'Construction', 'name' => 'Old Builders Ltd', 'category' => 'Construction',
        'phone' => '+256772000002', 'source' => 'yellow-pages',
        'hash' => md5('old'), 'fetched_at' => now()->subDays(30),
    ]);

    $this->actingAs($user)->get('/dashboard/prospects')
        ->assertOk()
        ->assertSee('>New</span>', false)
        ->assertSee('Yellow Pages Uganda', false)
        ->assertSee('https://www.yellowpages.co.ug/business-details/1-fresh', false);
});

test('ugandan whatsapp communities ship with real join links', function () {
    $groups = WhatsappGroup::where('is_active', true)->get();

    expect($groups->count())->toBeGreaterThanOrEqual(8)
        ->and(WhatsappGroup::where('invite_link', 'like', '%REPLACE%')->count())->toBe(0);

    foreach ($groups as $g) {
        expect($g->invite_link)->toStartWith('https://groupsor.link/group/join/')
            ->and($g->source)->toBe('groupsor.directory');
    }
});

test('saved contacts keep their source and exports include it', function () {
    $user = proUser();

    $this->actingAs($user)->postJson('/dashboard/scraper/save', [
        'niche' => 'Construction',
        'source' => 'web',
        'contacts' => [[
            'name' => 'Nile Builders Limited', 'type' => 'Construction',
            'district' => 'Kampala', 'phone' => '+256772123456',
            'source_url' => 'https://www.yellowpages.co.ug/business-details/1-nile',
        ]],
    ])->assertOk();

    $saved = SavedContact::first();
    expect($saved->source_url)->toBe('https://www.yellowpages.co.ug/business-details/1-nile');

    $this->actingAs($user)->get('/dashboard/contacts')
        ->assertOk()
        ->assertSee('https://www.yellowpages.co.ug/business-details/1-nile', false);

    $this->actingAs($user)->get('/dashboard/contacts/export')
        ->assertOk()
        ->assertSee('Source URL', false)
        ->assertSee('https://www.yellowpages.co.ug/business-details/1-nile', false);
});

test('scraper page states the pro cost and balance', function () {
    $this->actingAs(proUser(42))->get('/dashboard/scraper')
        ->assertOk()
        ->assertSee('Pro tool', false)
        ->assertSee('5 tokens', false)
        ->assertSee('42', false);
});
