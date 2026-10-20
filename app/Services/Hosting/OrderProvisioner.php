<?php

namespace App\Services\Hosting;

use App\Models\HostedWebsite;
use App\Models\HostingAuditLog;
use App\Models\HostingPlan;
use App\Models\PackageOrder;
use App\Models\WebsiteDomain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates the hosted-website record for a paid-for package order.
 *
 * The website starts as `pending_setup` and is NOT provisioned on the
 * server: an administrator reviews the captured domain/subdomain and
 * activates it. This keeps "create automatically, activate by admin" while
 * never exposing a half-configured site.
 */
class OrderProvisioner
{
    public static function createForOrder(PackageOrder $order): ?HostedWebsite
    {
        if ($order->hosted_website_id) {
            return HostedWebsite::find($order->hosted_website_id);
        }

        $user = $order->user;

        if (! $user) {
            return null;
        }

        $plan = HostingPlan::firstOrCreate(
            ['code' => $order->package],
            [
                'name' => $order->package,
                'price' => (int) ($order->total_amount ?: 0),
                'deposit' => '0%',
                'features' => [],
                'is_active' => true,
            ]
        );

        return DB::transaction(function () use ($order, $user, $plan) {
            $website = $user->hostedWebsites()->create([
                'uuid' => (string) Str::uuid(),
                'hosting_plan_id' => $plan->id,
                'name' => $order->business_name ?: ($order->package.' website'),
                'site_type' => $order->niche,
                'description' => $order->notes,
                'status' => HostedWebsite::STATUS_PENDING,
                'next_renewal_at' => null,
            ]);

            $primary = self::attachPrimaryDomain($website, $order);

            $website->forceFill(['primary_domain_id' => $primary->id])->save();

            $order->forceFill(['hosted_website_id' => $website->id])->save();

            HostingAuditLog::log('website.registered', [
                'actor_type' => 'system',
                'hosted_website_id' => $website->id,
                'client_id' => $user->id,
                'meta' => [
                    'order' => $order->reference,
                    'domain' => $primary->domain,
                    'status' => HostedWebsite::STATUS_PENDING,
                ],
            ]);

            return $website;
        });
    }

    /**
     * Pick the primary domain from what the customer captured: their own
     * domain, the chosen free subdomain, or a generated fallback label.
     */
    protected static function attachPrimaryDomain(HostedWebsite $website, PackageOrder $order): WebsiteDomain
    {
        if ($order->domain === 'subdomain' && $order->subdomain) {
            return $website->domains()->create([
                'user_id' => $website->user_id,
                'domain' => SubdomainGenerator::fullDomain($order->subdomain),
                'type' => WebsiteDomain::TYPE_SUBDOMAIN,
                'is_primary' => true,
                'verification' => 'verified',
            ]);
        }

        if ($order->domain === 'none' && $order->custom_domain) {
            return $website->domains()->create([
                'user_id' => $website->user_id,
                'domain' => strtolower($order->custom_domain),
                'type' => WebsiteDomain::TYPE_CUSTOM,
                'is_primary' => true,
                'verification' => 'pending',
                'verification_token' => 'ttryy-verification='.Str::random(32),
            ]);
        }

        $label = SubdomainGenerator::generate($order->business_name ?: $order->package);

        return $website->domains()->create([
            'user_id' => $website->user_id,
            'domain' => SubdomainGenerator::fullDomain($label),
            'type' => WebsiteDomain::TYPE_SUBDOMAIN,
            'is_primary' => true,
            'verification' => 'verified',
        ]);
    }
}
