<?php

namespace App\Services;

use App\Models\ScrapedProspect;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DirectoryScraper
{
    public const BASE_URL = 'https://www.yellowpages.co.ug';

    public const MAX_RECORDS = 30;

    /** Extra Yellow Pages search terms per niche for broader real coverage. */
    public const NICHE_TERMS = [
        'NGOs & Charities' => ['non-governmental organizations', 'foundations'],
        'Construction' => ['construction', 'contractors', 'building materials'],
        'IT & Software' => ['software', 'computers', 'technology'],
        'Marketing & Advertising' => ['advertising', 'marketing', 'media agencies'],
        'Cleaning & Facility Management' => ['cleaning', 'cleaning services'],
        'Security' => ['security', 'security guard'],
        'Catering & Food Services' => ['catering', 'restaurants'],
        'Printing & Branding' => ['printing', 'printers'],
        'Office Furniture' => ['furniture', 'office furniture'],
        'Accounting & Professional Services' => ['accountants', 'auditors', 'tax consultants'],
        'Logistics & Transport' => ['transport', 'logistics', 'clearing'],
        'Agriculture & Agribusiness' => ['agriculture', 'agro', 'farms'],
        'Solar & Renewable Energy' => ['solar', 'energy'],
        'Medical Suppliers' => ['pharmacies', 'medical', 'hospitals'],
    ];

    /** Serve cached index when it is this fresh (days). */
    public const CACHE_DAYS = 30;

    /**
     * Search the local prospect index first; live-scrape Yellow Pages
     * Uganda when the index is empty or stale, then persist everything
     * so users never have to scrape the same niche twice.
     *
     * @return array{records: array, total_found: int, source: string, cached: bool, fetched_at: ?string}
     */
    public static function search(string $niche, string $keyword): array
    {
        $keyword = trim($keyword) === '' ? $niche : trim($keyword);

        $cached = ScrapedProspect::query()
            ->where('niche', $niche)
            ->where('fetched_at', '>=', now()->subDays(self::CACHE_DAYS))
            ->latest('fetched_at')
            ->take(self::MAX_RECORDS)
            ->get();

        if ($cached->count() >= 10) {
            return [
                'records' => $cached->map->toResultArray()->all(),
                'total_found' => $cached->count(),
                'source' => 'yellow-pages',
                'cached' => true,
                'fetched_at' => $cached->first()->fetched_at?->format('d M Y'),
            ];
        }

        $fresh = self::scrapeLive($niche, $keyword);

        if ($fresh !== []) {
            return [
                'records' => array_slice($fresh, 0, self::MAX_RECORDS),
                'total_found' => count($fresh),
                'source' => 'yellow-pages',
                'cached' => false,
                'fetched_at' => now()->format('d M Y'),
            ];
        }

        return [
            'records' => [],
            'total_found' => 0,
            'source' => 'unavailable',
            'cached' => false,
            'fetched_at' => null,
        ];
    }

    /**
     * Fetch + parse Yellow Pages search results across several niche
     * terms, persisting every record into the local index (deduplicated).
     *
     * @return array<int, array>
     */
    public static function scrapeLive(string $niche, string $keyword): array
    {
        $terms = array_unique(array_filter(array_merge(
            [$keyword],
            self::NICHE_TERMS[$niche] ?? []
        )));

        $records = [];

        foreach (array_slice($terms, 0, 3) as $term) {
            $url = self::BASE_URL.'/search-results/'.urlencode(strtolower($term));

            try {
                $html = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) TtryyBot/1.0'])
                    ->timeout(30)
                    ->get($url)
                    ->throw()
                    ->body();
            } catch (\Throwable $e) {
                Log::warning('Directory scrape fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
                continue;
            }

            foreach (self::parseListings($html, $niche, $url) as $row) {
                $records[] = self::persist($row);
            }

            if (count($records) >= self::MAX_RECORDS) {
                break;
            }

            usleep(800000); // stay polite between requests
        }

        return $records;
    }

    /**
     * Parse Yellow Pages search-result cards out of raw HTML.
     *
     * @return array<int, array{name:string, category:?string, phone:?string, address:?string, district:?string, verified:bool, source_url:?string, detail_url:?string}>
     */
    public static function parseListings(string $html, string $niche, string $sourceUrl): array
    {
        $rows = [];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $cards = $xpath->query("//div[contains(@class,'rounded-lg') and contains(@class,'overflow-hidden') and contains(@class,'mb-4')]");

        foreach ($cards as $card) {
            $name = trim((string) $xpath->evaluate('string(.//h2)', $card));
            if ($name === '') {
                continue;
            }

            $categoryNode = $xpath->query(".//a[contains(@href,'/business-category/') and not(contains(@href,'business-details'))]", $card)->item(0);
            $category = $categoryNode ? trim($categoryNode->textContent) : null;

            $detailNode = $xpath->query(".//a[@title='View Listing']", $card)->item(0);
            $detailUrl = $detailNode ? self::BASE_URL.$detailNode->getAttribute('href') : null;

            $phoneNode = $xpath->query(".//a[starts-with(@href,'tel:')]", $card)->item(0);
            $phone = $phoneNode ? self::normalizePhone($phoneNode->getAttribute('href')) : null;

            $address = trim((string) $xpath->evaluate('string(.//address)', $card));
            $address = $address === '' ? null : preg_replace('/\s+/', ' ', $address);

            $verifiedText = $xpath->evaluate("string(.//span[contains(.,'Verified') or contains(.,'Unverified')])", $card);
            $verified = str_contains((string) $verifiedText, 'Verified') && ! str_contains((string) $verifiedText, 'Unverified');

            $district = null;
            if ($address && preg_match('/,\s*([A-Za-z ]+)$/', $address, $m)) {
                $district = trim($m[1]);
            }

            $rows[] = [
                'niche' => $niche,
                'name' => $name,
                'category' => $category,
                'phone' => $phone,
                'address' => $address,
                'district' => $district,
                'verified' => $verified,
                'source_url' => $sourceUrl,
                'detail_url' => $detailUrl,
            ];
        }

        return $rows;
    }

    public static function persist(array $row): array
    {
        $hash = md5(strtolower($row['name'].'|'.($row['phone'] ?? '').'|'.($row['category'] ?? '')));

        $record = ScrapedProspect::updateOrCreate(
            ['hash' => $hash],
            $row + ['hash' => $hash, 'source' => 'yellow-pages', 'fetched_at' => now()]
        );

        return $record->toResultArray();
    }

    /**
     * Normalize Ugandan numbers to full international format.
     * Handles 07XX mobile, 041/048 landlines and bare local digits.
     */
    public static function normalizePhone(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '' || $digits === null) {
            return null;
        }

        if (str_starts_with($digits, '256')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+256'.substr($digits, 1);
        }

        // Bare local digits (e.g. Kampala landline without area code context).
        return '+256'.$digits;
    }
}
