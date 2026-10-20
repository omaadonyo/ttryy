<?php

namespace App\Http\Middleware;

use App\Services\TtryyService\ServiceState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks visitors with HTTP 503 while this website's service state is
 * suspended. Content is never touched — only the response changes.
 *
 * Register MUTEX-FREE and make sure it NEVER applies to the service routes
 * (that would stop the platform from un-suspending the site). The route
 * file excludes 'hosting-service/*', so applying it to the whole web group
 * is safe; do not add it to the hosting-service routes.
 */
class EnsureSiteActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('ttryy-service.mode') === 'middleware'
            && ServiceState::isSuspended()
            && ! $request->is('hosting-service/*')) {
            return response()->view('ttryy-service.suspended', [
                'notice' => config('ttryy-service.notice', []),
            ], 503);
        }

        return $next($request);
    }
}
