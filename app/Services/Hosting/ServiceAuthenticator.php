<?php

namespace App\Services\Hosting;

use App\Models\HostedWebsite;
use App\Models\HostingCommand;
use App\Models\WebsiteServiceCredential;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * HMAC service-to-service authentication shared by the central platform
 * and the embeddable website service backend.
 *
 * Envelope (JSON body + headers):
 *   X-Service-Key, X-Service-Timestamp, X-Service-Nonce (= operation uuid),
 *   X-Service-Site (website uuid), X-Service-Signature
 * Signature = HMAC-SHA256(secret, timestamp\nonce\nMETHOD\npath\nbody_sha256\nsite_uuid)
 *
 * Secrets are stored APP_KEY-encrypted (HMAC verification needs the
 * plaintext); only a SHA-256 fingerprint is ever displayed or logged.
 */
class ServiceAuthenticator
{
    public const VERSION = '1.0.0';

    /**
     * @return array{0: WebsiteServiceCredential, 1: string} credential + plaintext secret (show once)
     */
    public static function issueCredential(HostedWebsite $website): array
    {
        $secret = Str::random(48);

        $credential = $website->credentials()->create([
            'key_id' => 'hs_'.Str::random(24),
            'secret_hash' => hash('sha256', $secret),
            'secret_encrypted' => Crypt::encryptString($secret),
        ]);

        return [$credential, $secret];
    }

    public static function sign(
        string $secret,
        string $method,
        string $path,
        string $body,
        string $timestamp,
        string $nonce,
        string $siteUuid
    ): string {
        $payload = implode("\n", [
            $timestamp, $nonce, strtoupper($method), $path,
            hash('sha256', $body), $siteUuid,
        ]);

        return hash_hmac('sha256', $payload, $secret);
    }

    /**
     * Verify an incoming service request.
     *
     * @return array{credential: WebsiteServiceCredential, website: HostedWebsite, replay: ?HostingCommand}
     *
     * @throws ServiceAuthException
     */
    public static function verifyRequest(
        string $method,
        string $path,
        string $body,
        ?string $keyId,
        ?string $timestamp,
        ?string $nonce,
        ?string $siteUuid,
        ?string $signature
    ): array {
        if (! $keyId || ! $timestamp || ! $nonce || ! $siteUuid || ! $signature) {
            throw new ServiceAuthException('Missing service credentials.');
        }

        if (abs(time() - (int) $timestamp) > (int) config('hosting.service_auth.tolerance', 300)) {
            throw new ServiceAuthException('Request expired.');
        }

        $credential = WebsiteServiceCredential::where('key_id', $keyId)
            ->whereNull('revoked_at')
            ->first();

        if (! $credential) {
            throw new ServiceAuthException('Invalid service credentials.');
        }

        // The credential belongs to exactly one website; the envelope must name it.
        $website = $credential->website;

        if (! $website || $website->uuid !== $siteUuid) {
            throw new ServiceAuthException('Credential does not belong to this website.');
        }

        try {
            $secret = Crypt::decryptString($credential->secret_encrypted);
        } catch (\Throwable) {
            throw new ServiceAuthException('Invalid service credentials.');
        }

        $expected = self::sign($secret, $method, $path, $body, $timestamp, $nonce, $siteUuid);

        if (! hash_equals($expected, $signature)) {
            throw new ServiceAuthException('Invalid signature.');
        }

        $credential->forceFill(['last_used_at' => now()])->save();

        // Replay protection: the nonce is the command operation uuid. A repeat
        // delivery returns the stored command instead of re-executing.
        $replay = HostingCommand::where('uuid', $nonce)->first();

        return ['credential' => $credential, 'website' => $website, 'replay' => $replay];
    }
}
