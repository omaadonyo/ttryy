<?php

namespace App\Services\TtryyService;

use Illuminate\Support\Facades\File;

/**
 * Tiny file-backed store for this website's service state.
 *
 * Suspension NEVER deletes content, databases or domains — it only flips a
 * single state value that the middleware reads. Swap this class for a
 * database/cache driver if you prefer; keep the same four methods.
 */
class ServiceState
{
    public static function read(): string
    {
        $path = (string) config('ttryy-service.state_path');

        if (! $path || ! File::isFile($path)) {
            return ServiceCommandProcessor::STATE_ACTIVE;
        }

        $data = json_decode((string) File::get($path), true);

        return is_array($data) && isset($data['state'])
            ? (string) $data['state']
            : ServiceCommandProcessor::STATE_ACTIVE;
    }

    public static function write(string $state, array $context = []): void
    {
        $path = (string) config('ttryy-service.state_path');
        $dir = dirname($path);

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        File::put($path, json_encode([
            'state' => $state,
            'context' => $context,
            'updated_at' => gmdate('c'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public static function isSuspended(): bool
    {
        return in_array(self::read(), [
            ServiceCommandProcessor::STATE_SUSPENDED,
            ServiceCommandProcessor::STATE_SUSPENSION_PENDING,
        ], true);
    }
}
