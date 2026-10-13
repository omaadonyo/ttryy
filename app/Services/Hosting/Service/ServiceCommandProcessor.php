<?php

namespace App\Services\Hosting\Service;

/**
 * Ttryy Hosted Website Service Backend (embeddable component, version 1.0.0).
 *
 * Drop this directory into any hosted Laravel website to give it a secure
 * service integration with the central Ttryy platform:
 *
 *   1. Copy this file (and nothing else) into the hosted app, e.g.
 *      app/Services/TtryyService/ServiceCommandProcessor.php (keep namespace sane).
 *   2. Add routes on the hosted site:
 *        POST /hosting-service/commands   -> verify HMAC, run ::execute(), return ack
 *        GET  /hosting-service/status     -> verify HMAC, return ::status()
 *        GET  /hosting-service/health     -> public liveness, minimal output
 *   3. Store the issued key id + secret in the hosted site's .env
 *      (HOSTING_SERVICE_KEY_ID / HOSTING_SERVICE_SECRET) — never in git.
 *   4. Configure suspension mode: 'middleware' (restrict visitors with 503,
 *      data is never deleted) or 'flag' (record state only, web server
 *      handles the rest). See docs in the central README.
 *
 * What suspension does here: flips the local service state and, in
 * middleware mode, makes the bundled middleware return HTTP 503 with a
 * neutral notice. It NEVER deletes files, databases, domains, or content,
 * and it NEVER touches other websites.
 */
class ServiceCommandProcessor
{
    public const VERSION = '1.0.0';

    public const STATE_ACTIVE = 'active';

    public const STATE_SUSPENSION_PENDING = 'suspension_pending';

    public const STATE_SUSPENDED = 'suspended';

    public const STATE_RESTORATION_PENDING = 'restoration_pending';

    public const STATE_ERROR = 'error';

    public const ALLOWED_ACTIONS = ['suspend', 'unsuspend', 'health_check', 'report_status'];

    /**
     * Execute one verified command against local state.
     *
     * @param  array{action: string, operation_id: string, payload?: array}  $command
     * @param  callable  $readState  fn(): string current state
     * @param  callable  $writeState  fn(string $state, array $context): void persist state
     */
    public static function execute(array $command, callable $readState, callable $writeState): array
    {
        $action = $command['action'] ?? '';
        $operationId = $command['operation_id'] ?? '';

        if (! in_array($action, self::ALLOWED_ACTIONS, true)) {
            return self::ack($operationId, $readState(), false, 'Action not allowed.');
        }

        // Idempotency is enforced centrally via the operation uuid; replays
        // arrive only if the first ack was lost, so re-applying is safe.
        $current = $readState();

        $next = match ($action) {
            'suspend' => self::STATE_SUSPENDED,
            'unsuspend' => self::STATE_ACTIVE,
            'health_check', 'report_status' => $current,
        };

        try {
            $writeState($next, ['operation_id' => $operationId, 'action' => $action]);
        } catch (\Throwable $e) {
            return self::ack($operationId, self::STATE_ERROR, false, 'Local state write failed.');
        }

        return self::ack($operationId, $next, true, null);
    }

    public static function ack(string $operationId, string $serviceState, bool $ok, ?string $message): array
    {
        return [
            'ok' => $ok,
            'operation_id' => $operationId,
            'service_state' => $serviceState,
            'service_version' => self::VERSION,
            'message' => $message,
        ];
    }

    /**
     * Minimal public liveness response. Reveals nothing about internals.
     */
    public static function liveness(): array
    {
        return ['ok' => true, 'service' => 'ttryy-hosting-service', 'version' => self::VERSION];
    }

    /**
     * Protected diagnostics. Never include credentials, env values,
     * connection strings, or stack traces.
     */
    public static function diagnostics(callable $readState, callable $checkDatabase): array
    {
        try {
            $db = (bool) $checkDatabase();
        } catch (\Throwable) {
            $db = false;
        }

        return [
            'service_state' => $readState(),
            'service_version' => self::VERSION,
            'app' => true,
            'database' => $db,
            'time' => gmdate('c'),
        ];
    }
}
