<?php

namespace App\Http\Controllers;

use App\Models\HostedWebsite;
use App\Services\Hosting\Service\ServiceCommandProcessor;
use Illuminate\Http\Request;

/**
 * Central ingest endpoints for hosted website services.
 * All routes here sit behind EnsureServiceSignature (HMAC).
 */
class ServiceApiController extends Controller
{
    public function heartbeat(Request $request)
    {
        $validated = $request->validate([
            'service_version' => ['nullable', 'string', 'max:20'],
            'service_state' => ['nullable', 'string', 'max:30'],
        ]);

        /** @var HostedWebsite $website */
        $website = $request->attributes->get('service_website');

        $website->forceFill([
            'last_heartbeat_at' => now(),
            'service_connected' => true,
            'service_version' => $validated['service_version'] ?? $website->service_version ?? ServiceCommandProcessor::VERSION,
            'service_state' => $validated['service_state'] ?? $website->service_state,
        ])->save();

        return response()->json(['ok' => true, 'server_time' => gmdate('c')]);
    }

    public function healthReport(Request $request)
    {
        $validated = $request->validate([
            'service_state' => ['nullable', 'string', 'max:30'],
            'service_version' => ['nullable', 'string', 'max:20'],
            'app' => ['nullable', 'boolean'],
            'database' => ['nullable', 'boolean'],
        ]);

        /** @var HostedWebsite $website */
        $website = $request->attributes->get('service_website');

        $app = $validated['app'] ?? true;
        $db = $validated['database'] ?? null;

        $status = HostedWebsite::HEALTH_HEALTHY;

        if (! $app) {
            $status = HostedWebsite::HEALTH_UNAVAILABLE;
        } elseif ($db === false) {
            $status = HostedWebsite::HEALTH_DEGRADED;
        }

        $website->healthChecks()->create([
            'status' => $status,
            'app_ok' => $app,
            'db_ok' => $db,
            'checked_at' => now(),
            'notes' => 'Reported by website service.',
        ]);

        $website->forceFill([
            'health_status' => $status,
            'last_health_check_at' => now(),
            'last_health_report_at' => now(),
            'service_connected' => true,
            'service_version' => $validated['service_version'] ?? $website->service_version,
            'service_state' => $validated['service_state'] ?? $website->service_state,
        ])->save();

        return response()->json(['ok' => true]);
    }

    public function status(Request $request)
    {
        /** @var HostedWebsite $website */
        $website = $request->attributes->get('service_website');

        return response()->json([
            'ok' => true,
            'uuid' => $website->uuid,
            'status' => $website->status,
            'health_status' => $website->health_status,
            'service_state' => $website->service_state,
            'service_connected' => (bool) $website->service_connected,
            'heartbeat_stale' => $website->heartbeatStale(),
            'last_heartbeat_at' => $website->last_heartbeat_at,
            'plan' => $website->plan?->code,
        ]);
    }
}
