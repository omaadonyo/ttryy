<?php

namespace App\Http\Controllers;

use App\Services\TtryyService\ServiceAuthenticator;
use App\Services\TtryyService\ServiceCommandProcessor;
use App\Services\TtryyService\ServiceState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Hosted-website service backend. Exposes three endpoints consumed by the
 * central ttryy platform. Keep this controller thin — all state lives in
 * ServiceState and all rules in ServiceCommandProcessor.
 */
class TtryyServiceController extends Controller
{
    /** Public liveness check — reveals nothing about the site. */
    public function health()
    {
        return response()->json(ServiceCommandProcessor::liveness());
    }

    /** Protected diagnostics (service state, app + database reachability). */
    public function status(Request $request)
    {
        if (! $this->verified($request, '/hosting-service/status')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json(ServiceCommandProcessor::diagnostics(
            fn () => ServiceState::read(),
            fn () => DB::connection()->getPdo() !== null,
        ));
    }

    /** Apply a signed suspend / unsuspend / health_check command. */
    public function commands(Request $request)
    {
        if (! $this->verified($request, '/hosting-service/commands')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $command = $request->json()->all();

        $ack = ServiceCommandProcessor::execute(
            $command,
            fn () => ServiceState::read(),
            fn (string $state, array $context) => ServiceState::write($state, $context),
        );

        return response()->json($ack);
    }

    protected function verified(Request $request, string $path): bool
    {
        return ServiceAuthenticator::verify(
            $request->method(),
            $path,
            $request->getContent(),
            [
                'key' => $request->header('X-Service-Key'),
                'timestamp' => $request->header('X-Service-Timestamp'),
                'nonce' => $request->header('X-Service-Nonce'),
                'site' => $request->header('X-Service-Site'),
                'signature' => $request->header('X-Service-Signature'),
            ],
        );
    }
}
