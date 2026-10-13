<?php

namespace App\Services\Hosting;

/**
 * Tiny hostname helper shared by controllers (kept out of models).
 */
class HostingControllerHelper
{
    public static function hostname(string $domain): bool
    {
        return (bool) preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.[a-z0-9-]{1,63})+$/i', $domain);
    }
}
