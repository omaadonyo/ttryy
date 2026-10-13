<?php

namespace App\Services\Hosting;

use App\Models\HostedWebsite;
use App\Models\WebsiteAnalyticsEvent;
use App\Models\WebsiteDailyAnalytics;
use Illuminate\Http\Request;

/**
 * First-party website analytics.
 *
 * Unique visitors are defined as a privacy-conscious pseudonymous
 * identifier: HMAC-SHA256(date | client IP | user agent), rotated daily,
 * so a visitor counts once per website per day without storing identities.
 * Sessions use the same key with the current hour, so a visit counts once
 * per hour. Never label raw page requests as unique visitors.
 */
class AnalyticsRecorder
{
    public static function visitorKey(HostedWebsite $website, string $ip, string $ua, string $date): string
    {
        return hash_hmac('sha256', $date.'|'.$ip.'|'.$ua, config('app.key').'|'.$website->uuid);
    }

    public static function record(HostedWebsite $website, Request $request): WebsiteAnalyticsEvent
    {
        $ip = $request->ip() ?? '0.0.0.0';
        $ua = substr((string) $request->userAgent(), 0, 300);
        $today = now()->toDateString();

        $referrer = substr((string) ($request->query('ref') ?? $request->headers->get('referer')), 0, 500) ?: null;

        return $website->analyticsEvents()->create([
            'visitor_key' => self::visitorKey($website, $ip, $ua, $today),
            'session_key' => hash_hmac('sha256', $today.'|'.now()->format('H').'|'.$ip.'|'.$ua, config('app.key').'|'.$website->uuid),
            'path' => '/'.ltrim(substr((string) $request->query('p', '/'), 0, 490), '/'),
            'referrer' => $referrer,
            'source' => $request->query('utm_source') ? substr((string) $request->query('utm_source'), 0, 50) : self::sourceFromReferrer($referrer),
            'device' => self::deviceFromUa($ua),
            'country' => $request->query('c') ? substr((string) $request->query('c'), 0, 2) : null,
        ]);
    }

    public static function sourceFromReferrer(?string $referrer): ?string
    {
        if (! $referrer) {
            return 'direct';
        }

        $host = strtolower((string) parse_url($referrer, PHP_URL_HOST));

        foreach (['google.' => 'google', 'bing.' => 'bing', 'facebook.' => 'facebook', 'instagram.' => 'instagram', 'x.com' => 'x', 'twitter.' => 'x', 'linkedin.' => 'linkedin', 'tiktok.' => 'tiktok', 'youtube.' => 'youtube', 'whatsapp' => 'whatsapp'] as $needle => $source) {
            if (str_contains($host, $needle)) {
                return $source;
            }
        }

        return 'referral';
    }

    public static function deviceFromUa(string $ua): string
    {
        $ua = strtolower($ua);

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobi') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }

        if ($ua === '') {
            return 'unknown';
        }

        return 'desktop';
    }

    /**
     * Roll one day of raw events into the daily aggregate table.
     */
    public static function aggregateDay(HostedWebsite $website, string $date): WebsiteDailyAnalytics
    {
        $events = WebsiteAnalyticsEvent::where('hosted_website_id', $website->id)
            ->whereDate('created_at', $date)
            ->get(['visitor_key', 'session_key', 'path', 'source', 'device']);

        $top = function ($key, $limit = 8) use ($events) {
            return $events->countBy($key)->sortDesc()->take($limit)
                ->map(fn ($c, $k) => ['label' => (string) $k, 'count' => $c])
                ->values()->all();
        };

        return WebsiteDailyAnalytics::updateOrCreate(
            ['hosted_website_id' => $website->id, 'date' => $date],
            [
                'uniques' => $events->unique('visitor_key')->count(),
                'visits' => $events->unique('session_key')->count(),
                'pageviews' => $events->count(),
                'top_pages' => $top('path'),
                'sources' => $top('source'),
                'devices' => $top('device'),
            ]
        );
    }
}
