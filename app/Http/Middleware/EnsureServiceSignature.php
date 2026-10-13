<?php

namespace App\Http\Middleware;

use App\Services\Hosting\ServiceAuthenticator;
use App\Services\Hosting\ServiceAuthException;
use Closure;
use Illuminate\Http\Request;

/**
 * Authenticates website service ingest calls (heartbeat / health-report)
 * using the shared HMAC envelope. On success the resolved credential and
 * website are attached to the request. Replay deliveries short-circuit
 * with the stored command result.
 */
class EnsureServiceSignature
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $result = ServiceAuthenticator::verifyRequest(
                $request->method(),
                '/'.ltrim($request->path(), '/'),
                $request->getContent(),
                $request->header('X-Service-Key'),
                $request->header('X-Service-Timestamp'),
                $request->header('X-Service-Nonce'),
                $request->header('X-Service-Site'),
                $request->header('X-Service-Signature')
            );
        } catch (ServiceAuthException $e) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized.'], 401);
        }

        if ($result['replay']) {
            return response()->json([
                'ok' => true,
                'replay' => true,
                'operation_id' => $result['replay']->uuid,
                'state' => $result['replay']->state,
            ]);
        }

        $request->attributes->set('service_credential', $result['credential']);
        $request->attributes->set('service_website', $result['website']);

        return $next($request);
    }
}
