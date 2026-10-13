<?php

namespace App\Services\Hosting;

use App\Models\WebsiteDomain;
use Illuminate\Support\Str;

class SubdomainGenerator
{
    /**
     * Build a valid, unique subdomain label for the given website name.
     */
    public static function generate(string $name, ?int $ignoreWebsiteId = null): string
    {
        $base = Str::slug($name, '-');
        $base = preg_replace('/[^a-z0-9-]/', '', strtolower($base));
        $base = trim($base, '-');
        $base = substr($base !== '' ? $base : 'site', 0, 40);

        if (self::isReserved($base) || self::isTaken($base.'.'.config('hosting.base_domain'), $ignoreWebsiteId)) {
            $suffix = 2;
            while (self::isTaken($base.'-'.$suffix.'.'.config('hosting.base_domain'), $ignoreWebsiteId)) {
                $suffix++;
            }
            $base = $base.'-'.$suffix;
        }

        return $base;
    }

    public static function fullDomain(string $label): string
    {
        return strtolower($label).'.'.config('hosting.base_domain');
    }

    public static function isReserved(string $label): bool
    {
        return in_array(strtolower($label), config('hosting.reserved_subdomains', []), true);
    }

    public static function validLabel(string $label): bool
    {
        return (bool) preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', strtolower($label));
    }

    public static function isTaken(string $domain, ?int $ignoreWebsiteId = null): bool
    {
        $query = WebsiteDomain::where('domain', strtolower($domain));

        if ($ignoreWebsiteId) {
            $query->where('hosted_website_id', '!=', $ignoreWebsiteId);
        }

        return $query->exists();
    }
}
