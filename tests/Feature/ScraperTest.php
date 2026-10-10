<?php

use App\Services\DirectoryScraper;
use Illuminate\Support\Facades\Http;

function sampleListingHtml(): string
{
    return <<<'HTML'
    <html><body>
    <div class="pt-4 pb-4 grid grid-cols-1 gap-4">
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-4 shadow-lg">
        <div class="flex flex-col md:flex-row items-center justify-between p-4">
          <div class="flex flex-col md:flex-1 md:ml-4">
            <div class="pt-2 flex items-center gap-2 flex-wrap">
              <a title="Roads Construction, Equipment &amp; Supplies" href="/business-category/roads-construction-equipment-supplies"><span>Roads Construction, Equipment &amp; Supplies</span></a>
              <span class="inline-flex">Verified</span>
            </div>
            <h2 class="text-center text-2xl font-bold mt-2 text-black ">Nile Builders Limited</h2>
            <address class="pt-4 not-italic text-black ">Plot 12, Jinja Road<div>Kampala, Kampala</div></address>
            <div class="pt-4 flex flex-wrap"><div class="flex items-center mr-4 mb-2">
              <a title="Call Nile Builders Limited" href="tel:+256772123456"><span>Telephone</span></a>
            </div></div>
          </div>
        </div>
        <div class="flex justify-end p-4">
          <a title="View Listing" href="/business-category/roads-construction-equipment-supplies/business-details/1-nile-builders-limited"><span>View Listing</span></a>
        </div>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-4 shadow-lg">
        <div class="flex flex-col md:flex-row items-center justify-between p-4">
          <div class="flex flex-col md:flex-1 md:ml-4">
            <div class="pt-2 flex items-center gap-2 flex-wrap">
              <a title="Construction Management" href="/business-category/construction-management"><span>CM</span></a>
              <span class="inline-flex">Unverified</span>
            </div>
            <h2 class="text-center text-2xl font-bold mt-2 text-black ">Pearl Estates Ltd</h2>
            <address class="pt-4 not-italic text-black ">Entebbe Road<div>Wakiso, Entebbe</div></address>
            <div class="pt-4 flex flex-wrap"><div class="flex items-center mr-4 mb-2">
              <a title="Call Pearl Estates Ltd" href="tel:(41)456789"><span>Telephone</span></a>
            </div></div>
          </div>
        </div>
        <div class="flex justify-end p-4">
          <a title="View Listing" href="/business-category/construction-management/business-details/2-pearl-estates-ltd"><span>View Listing</span></a>
        </div>
      </div>
    </div>
    </body></html>
    HTML;
}

test('parser extracts real listing fields from Yellow Pages markup', function () {
    $rows = DirectoryScraper::parseListings(sampleListingHtml(), 'Construction', 'https://example.test/s');

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['name'])->toBe('Nile Builders Limited')
        ->and($rows[0]['category'])->toContain('Roads Construction')
        ->and($rows[0]['phone'])->toBe('+256772123456')
        ->and($rows[0]['verified'])->toBeTrue()
        ->and($rows[1]['phone'])->toBe('+25641456789')
        ->and($rows[1]['verified'])->toBeFalse()
        ->and($rows[1]['district'])->toBe('Entebbe');
});

test('phone normalization handles Ugandan formats', function () {
    expect(DirectoryScraper::normalizePhone('+256 772 123 456'))->toBe('+256772123456')
        ->and(DirectoryScraper::normalizePhone('0772 123456'))->toBe('+256772123456')
        ->and(DirectoryScraper::normalizePhone('(41)456789'))->toBe('+25641456789')
        ->and(DirectoryScraper::normalizePhone(null))->toBeNull()
        ->and(DirectoryScraper::normalizePhone('abc'))->toBeNull();
});

test('scraper endpoint serves the local index without refetching', function () {
    $user = App\Models\User::factory()->create();

    for ($i = 1; $i <= 10; $i++) {
        App\Models\ScrapedProspect::create([
            'niche' => 'Construction', 'name' => "Indexed Builders {$i} Ltd", 'category' => 'Construction',
            'phone' => '+2567000001'.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'district' => 'Kampala', 'verified' => true,
            'source' => 'yellow-pages', 'hash' => md5('indexed-'.$i), 'fetched_at' => now(),
        ]);
    }

    Http::fake(function () {
        throw new RuntimeException('network must not be touched on cache hit');
    });

    $this->actingAs($user)->postJson(route('scraper.search'), [
        'niche' => 'Construction',
        'keyword' => 'construction',
    ])->assertOk()->assertJsonPath('source', 'yellow-pages')->assertJsonPath('cached', true);
});

test('live scrape results are persisted and deduplicated', function () {
    Http::fake([
        'https://www.yellowpages.co.ug/*' => Http::response(sampleListingHtml(), 200),
    ]);

    $user = App\Models\User::factory()->create();

    $first = $this->actingAs($user)->postJson(route('scraper.search'), [
        'niche' => 'Construction',
        'keyword' => 'construction',
    ])->assertOk();

    expect(App\Models\ScrapedProspect::count())->toBe(2);

    // Second identical scrape must not duplicate rows.
    $this->actingAs($user)->postJson(route('scraper.search'), [
        'niche' => 'Construction',
        'keyword' => 'construction',
    ])->assertOk();

    expect(App\Models\ScrapedProspect::count())->toBe(2)
        ->and($first->json('cached'))->toBeFalse();
});

test('email campaigns send, track opens and report', function () {    Illuminate\Support\Facades\Mail::fake();
    $user = App\Models\User::factory()->create();

    $send = $this->actingAs($user)->postJson(route('marketing.campaigns.send'), [
        'subject' => 'Hello {business}',
        'body' => 'Hi {name}, quick one.',
        'emails' => 'jane@company.com, not-an-email, brian@business.co.ug',
    ])->assertOk();

    expect($send->json('sent'))->toBe(2)->and($send->json('failed'))->toBe(0);

    $campaign = App\Models\EmailCampaign::first();
    expect($campaign->recipients()->count())->toBe(2);

    $token = $campaign->recipients()->first()->token;
    $this->get(route('tracking.open', $token))->assertOk();
    $this->get(route('tracking.open', $token))->assertOk();

    expect($campaign->fresh()->recipients()->whereNotNull('opened_at')->count())->toBe(1);

    $this->actingAs($user)->get(route('marketing.campaigns.show', $campaign))
        ->assertOk()
        ->assertSee('Open rate', false);
});

test('prospects catalogue filters by search, niche and verified', function () {
    App\Models\ScrapedProspect::create([
        'niche' => 'Construction', 'name' => 'Alpha Builders Ltd', 'category' => 'Contractors',
        'phone' => '+256700000001', 'district' => 'Kampala', 'verified' => true,
        'source' => 'web', 'hash' => md5('alpha'), 'fetched_at' => now(),
    ]);
    App\Models\ScrapedProspect::create([
        'niche' => 'Medical Suppliers', 'name' => 'Beta Pharma Ltd', 'category' => 'Pharmacies',
        'phone' => '+256700000002', 'district' => 'Entebbe', 'verified' => false,
        'source' => 'web', 'hash' => md5('beta'), 'fetched_at' => now(),
    ]);

    $user = App\Models\User::factory()->create();

    $this->actingAs($user)->get(route('prospects.index'))
        ->assertOk()
        ->assertSee('Alpha Builders Ltd', false)
        ->assertSee('Beta Pharma Ltd', false);

    $this->actingAs($user)->get(route('prospects.index', ['q' => 'Alpha']))
        ->assertOk()
        ->assertSee('Alpha Builders Ltd', false)
        ->assertDontSee('Beta Pharma Ltd', false);

    $this->actingAs($user)->get(route('prospects.index', ['niche' => 'Medical Suppliers']))
        ->assertOk()
        ->assertSee('Beta Pharma Ltd', false)
        ->assertDontSee('Alpha Builders Ltd', false);

    $this->actingAs($user)->get(route('prospects.index', ['verified' => 1]))
        ->assertOk()
        ->assertSee('Alpha Builders Ltd', false)
        ->assertDontSee('Beta Pharma Ltd', false);
});

test('prospects catalogue requires login', function () {
    $this->get(route('prospects.index'))->assertRedirect(route('login'));
});

test('marketing page lists whatsapp groups with search and filter', function () {
    $this->seed(Database\Seeders\MarketingSeeder::class);
    $user = App\Models\User::factory()->create();

    $this->actingAs($user)->get(route('marketing.index'))
        ->assertOk()
        ->assertSee('WhatsApp growth communities', false)
        ->assertSee('Uganda SME Network', false);
});

test('group suggestions validate input and accept valid ones', function () {
    $user = App\Models\User::factory()->create();

    $this->actingAs($user)->postJson(route('marketing.groups.suggest'), [
        'name' => 'Test',
        'invite_link' => 'not-a-url',
    ])->assertUnprocessable();

    $this->actingAs($user)->postJson(route('marketing.groups.suggest'), [
        'name' => 'Kampala Traders',
        'niche' => 'Retail',
        'invite_link' => 'https://chat.whatsapp.com/AbCdEfGhIjKlMnOpQrSt',
        'description' => 'Buy and sell group.',
    ])->assertOk();
});
