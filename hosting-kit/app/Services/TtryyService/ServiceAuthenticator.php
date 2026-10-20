<?php

namespace App\Services\TtryyService;

/**
 * Verifies incoming service requests from the central ttryy platform.
 *
 * Signature = HMAC-SHA256(secret, "timestamp\nnonce\nMETHOD\npath\nsha256(body)\nsite_uuid")
 * carried in the X-Service-* headers. This mirrors the platform's
 * ServiceAuthenticator exactly.
 */
class ServiceAuthenticator
{
    public static function verify(string $method, string $path, string $body, array $headers): bool
    {
        $key = (string) ($headers['key'] ?? '');
        $timestamp = (string) ($headers['timestamp'] ?? '');
        $nonce = (string) ($headers['nonce'] ?? '');
        $site = (string) ($headers['site'] ?? '');
        $signature = (string) ($headers['signature'] ?? '');

        if ($key === '' || $timestamp === '' || $nonce === '' || $site === '' || $signature === '') {
            return false;
        }

        if (abs(time() - (int) $timestamp) > (int) config('ttryy-service.tolerance', 300)) {
            return false;
        }

        if (! hash_equals((string) config('ttryy-service.key_id'), $key)) {
            return false;
        }

        $expectedSite = (string) config('ttryy-service.site_uuid');

        if ($expectedSite !== '' && ! hash_equals($expectedSite, $site)) {
            return false;
        }

        $expected = hash_hmac('sha256', implode("\n", [
            $timestamp, $nonce, strtoupper($method), $path,
            hash('sha256', $body), $site,
        ]), (string) config('ttryy-service.secret'));

        return hash_equals($expected, $signature);
    }
}
