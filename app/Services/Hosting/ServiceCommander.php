<?php

namespace App\Services\Hosting;

use App\Models\HostedWebsite;
use App\Models\HostingAuditLog;
use App\Models\HostingCommand;
use App\Notifications\SiteAlert;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Delivers central hosting commands to a website's service backend over HTTPS.
 * Nothing is marked succeeded until the service acknowledges it.
 */
class ServiceCommander
{
    /**
     * Build (but do not send) a command for a website.
     */
    public static function build(
        HostedWebsite $website,
        string $action,
        string $actorType = 'system',
        ?int $actorId = null,
        array $payload = []
    ): HostingCommand {
        return $website->commands()->create([
            'uuid' => (string) Str::uuid(),
            'action' => $action,
            'state' => HostingCommand::STATE_PENDING,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'payload' => $payload,
        ]);
    }

    /**
     * Attempt delivery. Returns the updated command.
     */
    public static function deliver(HostingCommand $command): HostingCommand
    {
        $website = $command->website()->first();

        if (! $website) {
            return self::fail($command, 'Website no longer exists.', 'website_missing');
        }

        if (! $website->service_url) {
            return self::fail($command, 'Website service endpoint is not configured.', 'service_not_connected');
        }

        $credential = $website->activeCredential();

        if (! $credential) {
            return self::fail($command, 'No active service credential for this website.', 'no_credentials');
        }

        try {
            $secret = Crypt::decryptString($credential->secret_encrypted);
        } catch (\Throwable) {
            return self::fail($command, 'Service credential cannot be decrypted.', 'credential_error');
        }

        $command->forceFill([
            'state' => HostingCommand::STATE_PROCESSING,
            'attempts' => $command->attempts + 1,
        ])->save();

        $website->forceFill(['last_command_at' => now()])->save();

        $body = json_encode([
            'operation_id' => $command->uuid,
            'action' => $command->action,
            'site_uuid' => $website->uuid,
            'payload' => $command->payload ?? [],
        ]);

        $timestamp = (string) time();
        $path = '/hosting-service/commands';
        $url = rtrim($website->service_url, '/').$path;

        $signature = ServiceAuthenticator::sign(
            $secret, 'POST', $path, $body, $timestamp, $command->uuid, $website->uuid
        );

        try {
            $response = Http::timeout(10)->withHeaders([
                'Content-Type' => 'application/json',
                'X-Service-Key' => $credential->key_id,
                'X-Service-Timestamp' => $timestamp,
                'X-Service-Nonce' => $command->uuid,
                'X-Service-Site' => $website->uuid,
                'X-Service-Signature' => $signature,
            ])->post($url, json_decode($body, true));
        } catch (\Throwable $e) {
            return self::maybeRetry($command, 'Service unreachable: '.$e->getMessage());
        }

        if (! $response->successful()) {
            return self::maybeRetry($command, 'Service returned HTTP '.$response->status().'.');
        }

        $ack = $response->json();

        if (! is_array($ack) || ($ack['ok'] ?? false) !== true) {
            return self::maybeRetry($command, 'Service did not acknowledge the command.');
        }

        $command->forceFill([
            'state' => HostingCommand::STATE_SUCCEEDED,
            'response' => $ack,
            'error' => null,
        ])->save();

        static::applyAck($website->fresh(), $command);

        return $command->fresh();
    }

    /**
     * Apply an acknowledged result to website state. Only runs on success acks.
     */
    public static function applyAck(HostedWebsite $website, HostingCommand $command): void
    {
        $reported = $command->response['service_state'] ?? null;

        $updates = ['service_connected' => true];

        if (is_string($reported)) {
            $updates['service_state'] = $reported;
        }

        match ($command->action) {
            HostingCommand::ACTION_SUSPEND => $updates += [
                'status' => HostedWebsite::STATUS_SUSPENDED,
                'suspended_at' => now(),
                'suspension_reason' => $command->payload['reason'] ?? 'Administrative suspension',
            ],
            HostingCommand::ACTION_UNSUSPEND => $updates += [
                'status' => HostedWebsite::STATUS_ACTIVE,
                'suspended_at' => null,
                'suspension_reason' => null,
                'failed_at' => null,
                'fail_reason' => null,
            ],
            HostingCommand::ACTION_PROVISION => $updates += [
                'status' => HostedWebsite::STATUS_ACTIVE,
                'failed_at' => null,
                'fail_reason' => null,
            ],
            default => [],
        };

        if (isset($command->response['service_version'])) {
            $updates['service_version'] = $command->response['service_version'];
        }

        $website->forceFill($updates)->save();

        HostingAuditLog::log('command.'.$command->action.'.acked', [
            'actor_type' => $command->actor_type,
            'actor_id' => $command->actor_id,
            'hosted_website_id' => $website->id,
            'client_id' => $website->user_id,
            'operation_id' => $command->uuid,
            'meta' => ['service_state' => $updates['service_state'] ?? $website->service_state],
        ]);

        $user = $website->user()->first();

        if ($user && in_array($command->action, [
            HostingCommand::ACTION_SUSPEND,
            HostingCommand::ACTION_UNSUSPEND,
            HostingCommand::ACTION_PROVISION,
        ], true)) {
            $titles = [
                HostingCommand::ACTION_SUSPEND => "Website {$website->name} suspended",
                HostingCommand::ACTION_UNSUSPEND => "Website {$website->name} restored",
                HostingCommand::ACTION_PROVISION => "Website {$website->name} is live",
            ];
            $user->notify(new SiteAlert(
                $titles[$command->action],
                $command->action === HostingCommand::ACTION_SUSPEND
                    ? 'Reason: '.($command->payload['reason'] ?? 'administrative decision')
                    : 'Confirmed by the website service just now.',
                route('hosting.show', $website),
                'hosting'
            ));
        }
    }

    protected static function fail(HostingCommand $command, string $error, string $reason): HostingCommand
    {
        $command->forceFill([
            'state' => HostingCommand::STATE_FAILED,
            'error' => $error,
            'attempts' => $command->attempts + 1,
        ])->save();

        HostingAuditLog::log('command.'.$command->action.'.failed', [
            'actor_type' => $command->actor_type,
            'actor_id' => $command->actor_id,
            'hosted_website_id' => $command->hosted_website_id,
            'operation_id' => $command->uuid,
            'outcome' => 'failure',
            'reason' => $reason,
        ]);

        return $command->fresh();
    }

    protected static function maybeRetry(HostingCommand $command, string $error): HostingCommand
    {
        $attempts = $command->attempts;

        if ($attempts >= 3) {
            return self::fail($command, $error, 'max_attempts');
        }

        $command->forceFill([
            'state' => HostingCommand::STATE_RETRY,
            'error' => $error,
        ])->save();

        return $command->fresh();
    }
}
