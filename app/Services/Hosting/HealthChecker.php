<?php

namespace App\Services\Hosting;

use App\Models\HostedWebsite;
use App\Models\HostingAuditLog;
use App\Models\WebsiteHealthCheck;
use App\Notifications\SiteAlert;
use Illuminate\Support\Facades\Http;

/**
 * Central availability checks against a website's primary domain.
 * Honest by design: only the public HTTP surface is probed here.
 * Application/database health comes from the service backend's own reports.
 */
class HealthChecker
{
    public static function checkWebsite(HostedWebsite $website): WebsiteHealthCheck
    {
        $domain = $website->primaryDomain()->first();
        $previous = $website->health_status;
        $now = now();

        if (! $domain) {
            return self::record($website, [
                'status' => HostedWebsite::HEALTH_UNKNOWN,
                'notes' => 'No primary domain assigned yet.',
            ], $previous, $now);
        }

        $started = microtime(true);

        try {
            $response = Http::timeout((int) config('hosting.health.timeout_seconds', 8))->get($domain->url());
            $ms = (int) round((microtime(true) - $started) * 1000);
            $code = $response->status();

            if ($code >= 200 && $code < 400) {
                $status = $ms > 5000
                    ? HostedWebsite::HEALTH_DEGRADED
                    : HostedWebsite::HEALTH_HEALTHY;
            } elseif ($code >= 400 && $code < 500) {
                $status = HostedWebsite::HEALTH_DEGRADED;
            } else {
                $status = HostedWebsite::HEALTH_UNAVAILABLE;
            }

            return self::record($website, [
                'status' => $status,
                'http_status' => $code,
                'response_ms' => $ms,
                'dns_ok' => true,
                'notes' => 'HTTP '.$code.' in '.$ms.'ms.',
            ], $previous, $now);
        } catch (\Throwable $e) {
            return self::record($website, [
                'status' => HostedWebsite::HEALTH_UNAVAILABLE,
                'notes' => 'Unreachable: '.substr($e->getMessage(), 0, 180),
            ], $previous, $now);
        }
    }

    protected static function record(
        HostedWebsite $website,
        array $data,
        string $previous,
        \DateTimeInterface $now
    ): WebsiteHealthCheck {
        $incident = $data['status'] === HostedWebsite::HEALTH_UNAVAILABLE
            && $previous !== HostedWebsite::HEALTH_UNAVAILABLE;

        $check = $website->healthChecks()->create($data + [
            'incident' => $incident,
            'checked_at' => $now,
        ]);

        $website->forceFill([
            'health_status' => $data['status'],
            'last_health_check_at' => $now,
        ])->save();

        if ($incident) {
            HostingAuditLog::log('health.incident', [
                'hosted_website_id' => $website->id,
                'client_id' => $website->user_id,
                'outcome' => 'failure',
                'reason' => $data['notes'] ?? null,
            ]);

            $user = $website->user()->first();

            if ($user) {
                $user->notify(new SiteAlert(
                    "Website {$website->name} is unreachable",
                    'Our monitor could not reach it just now. We will keep checking.',
                    route('hosting.health', $website),
                    'hosting'
                ));
            }
        }

        return $check;
    }
}
