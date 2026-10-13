<?php

namespace App\Services\Hosting;

use App\Models\HostingAuditLog;
use App\Models\WebsiteDomain;
use Illuminate\Support\Facades\Http;

/**
 * Creates the subdomain on the real server when infrastructure is configured.
 *
 * Recommended setup (zero per-subdomain work): a wildcard DNS A record
 * (*.ttryy.com) plus a wildcard virtual host on the server. Database
 * records then resolve instantly and this provisioner only verifies DNS.
 *
 * Optional cPanel driver: set HOSTING_PROVISION_DRIVER=cpanel together with
 * HOSTING_CPANEL_HOST / HOSTING_CPANEL_USER / HOSTING_CPANEL_TOKEN and each
 * subdomain is created via the cPanel UAPI. Anything else is reported
 * honestly — never simulated.
 */
class SubdomainProvisioner
{
    /**
     * @return array{ok: bool, mode: string, message: string}
     */
    public static function provision(WebsiteDomain $domain): array
    {
        if ($domain->type !== WebsiteDomain::TYPE_SUBDOMAIN) {
            return ['ok' => true, 'mode' => 'n/a', 'message' => 'Custom domains are verified, not provisioned.'];
        }

        $driver = config('hosting.provisioning.driver', 'manual');

        $result = match ($driver) {
            'cpanel' => self::viaCpanel($domain),
            default => [
                'ok' => true,
                'mode' => 'wildcard',
                'message' => 'Recorded for wildcard routing (*.'.config('hosting.base_domain').'). Point a wildcard A record at the server once — no per-subdomain server work needed.',
            ],
        };

        try {
            $dnsOk = self::dnsResolves($domain->domain);
            $domain->forceFill(['dns_ok' => $dnsOk, 'dns_checked_at' => now()])->save();
            $result['dns_resolves'] = $dnsOk;
        } catch (\Throwable) {
        }

        HostingAuditLog::log('domain.provisioned', [
            'hosted_website_id' => $domain->hosted_website_id,
            'client_id' => $domain->user_id,
            'outcome' => $result['ok'] ? 'success' : 'failure',
            'reason' => $result['ok'] ? null : ($result['message'] ?? null),
            'meta' => ['domain' => $domain->domain, 'mode' => $result['mode']],
        ]);

        return $result;
    }

    protected static function viaCpanel(WebsiteDomain $domain): array
    {
        $host = config('hosting.cpanel.host');
        $user = config('hosting.cpanel.user');
        $token = config('hosting.cpanel.token');

        if (! $host || ! $user || ! $token) {
            return [
                'ok' => false,
                'mode' => 'cpanel',
                'message' => 'cPanel driver selected but HOSTING_CPANEL_HOST/USER/TOKEN are not all set.',
            ];
        }

        $base = config('hosting.base_domain');
        $label = preg_replace('/\.'.preg_quote($base, '/').'$/', '', $domain->domain);

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Authorization' => "cpanel {$user}:{$token}"])
                ->asForm()
                ->post(rtrim($host, '/').'/execute/SubDomain/addsubdomain', [
                    'domain' => $label,
                    'rootdomain' => $base,
                    'dir' => 'public_html/subs/'.$label,
                ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'mode' => 'cpanel', 'message' => 'cPanel unreachable: '.substr($e->getMessage(), 0, 140)];
        }

        $json = $response->json();

        if ($response->successful() && ($json['status'] ?? 0) == 1) {
            return ['ok' => true, 'mode' => 'cpanel', 'message' => 'Subdomain created on the server.'];
        }

        return ['ok' => false, 'mode' => 'cpanel', 'message' => 'cPanel refused: '.substr((string) ($json['errors'][0] ?? $response->body()), 0, 160)];
    }

    protected static function dnsResolves(string $domain): bool
    {
        try {
            $records = dns_get_record($domain, DNS_A);
        } catch (\Throwable) {
            return false;
        }

        return ! empty($records);
    }
}
