<?php

namespace App\Services\Hosting;

use App\Models\HostedWebsite;
use App\Models\WebsiteDomain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Shared domain operations used by the admin panel (clients are read-only).
 * Every method throws ValidationException with field errors on failure.
 */
class DomainManager
{
    public static function addSubdomain(HostedWebsite $website, int $userId, ?string $label = null): WebsiteDomain
    {
        $label = $label ? strtolower(trim($label)) : SubdomainGenerator::generate($website->name, $website->id);

        if (! SubdomainGenerator::validLabel($label)) {
            throw ValidationException::withMessages(['label' => 'Use letters, numbers and hyphens only (no leading/trailing hyphen).']);
        }

        if (SubdomainGenerator::isReserved($label)) {
            throw ValidationException::withMessages(['label' => 'That subdomain is reserved for system use.']);
        }

        $domain = SubdomainGenerator::fullDomain($label);

        if (WebsiteDomain::where('domain', $domain)->exists()) {
            throw ValidationException::withMessages(['label' => 'That subdomain is already taken. Try another.']);
        }

        $record = $website->domains()->create([
            'user_id' => $userId,
            'domain' => $domain,
            'type' => WebsiteDomain::TYPE_SUBDOMAIN,
            'is_primary' => $website->domains()->count() === 0,
            'verification' => 'verified',
        ]);

        if ($website->domains()->count() === 1) {
            $website->forceFill(['primary_domain_id' => $record->id])->save();
        }

        SubdomainProvisioner::provision($record->fresh());

        return $record->fresh();
    }

    public static function addCustomDomain(HostedWebsite $website, int $userId, string $domain): WebsiteDomain
    {
        $domain = strtolower(trim($domain));

        if (! HostingControllerHelper::hostname($domain)) {
            throw ValidationException::withMessages(['domain' => 'Enter a valid domain name.']);
        }

        if ($domain === config('hosting.base_domain') || str_ends_with($domain, '.'.config('hosting.base_domain'))) {
            throw ValidationException::withMessages(['domain' => 'That belongs to Ttryy — use subdomain generation instead.']);
        }

        if (WebsiteDomain::where('domain', $domain)->exists()) {
            throw ValidationException::withMessages(['domain' => 'That domain is already registered on Ttryy.']);
        }

        return $website->domains()->create([
            'user_id' => $userId,
            'domain' => $domain,
            'type' => WebsiteDomain::TYPE_CUSTOM,
            'is_primary' => false,
            'verification' => 'pending',
            'verification_token' => 'ttryy-verification='.Str::random(32),
        ]);
    }

    /**
     * Live DNS TXT check. Returns true when the challenge token is found.
     */
    public static function verify(WebsiteDomain $domain): bool
    {
        if ($domain->type === WebsiteDomain::TYPE_SUBDOMAIN) {
            return true;
        }

        $found = false;

        try {
            $records = dns_get_record('_ttryy-challenge.'.$domain->domain, DNS_TXT);

            foreach ($records ?: [] as $record) {
                if (isset($record['txt']) && str_contains($record['txt'], (string) $domain->verification_token)) {
                    $found = true;
                    break;
                }
            }
        } catch (\Throwable) {
            $found = false;
        }

        $domain->forceFill([
            'verification' => $found ? 'verified' : 'pending',
            'dns_ok' => $found,
            'dns_checked_at' => now(),
            'ssl_status' => $found ? 'pending' : $domain->ssl_status,
        ])->save();

        return $found;
    }

    public static function setPrimary(HostedWebsite $website, WebsiteDomain $domain): void
    {
        abort_if($domain->hosted_website_id !== $website->id, 404);

        if (! $domain->isVerified()) {
            throw ValidationException::withMessages(['domain' => 'Only verified domains can be primary.']);
        }

        DB::transaction(function () use ($website, $domain) {
            // Direct queries: the in-memory models may already read is_primary
            // as true, which would make save() a dirty-check no-op.
            $website->domains()->update(['is_primary' => false]);
            $website->domains()->where('id', $domain->id)->update(['is_primary' => true]);
            $website->forceFill(['primary_domain_id' => $domain->id])->save();
            $domain->refresh();
            $website->refresh();
        });
    }

    public static function destroy(HostedWebsite $website, WebsiteDomain $domain): string
    {
        abort_if($domain->hosted_website_id !== $website->id, 404);
        abort_if($domain->is_primary, 422, 'The primary domain cannot be removed.');

        $name = $domain->domain;
        $domain->delete();

        return $name;
    }
}
