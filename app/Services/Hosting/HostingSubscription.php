<?php

namespace App\Services\Hosting;

use App\Jobs\ProcessHostingCommand;
use App\Models\HostedWebsite;
use App\Models\HostingAuditLog;
use App\Models\HostingCommand;
use App\Models\PackageOrder;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Ties hosting access to the order billing rhythm (daily / weekly /
 * monthly / full). Payments extend the renewal date; the daily scheduler
 * suspends websites that fall overdue with a real balance outstanding.
 */
class HostingSubscription
{
    public static function periodEnd(\DateTimeInterface $from, string $frequency): CarbonInterface
    {
        $date = Carbon::instance($from);

        return match ($frequency) {
            'daily' => $date->addDay(),
            'weekly' => $date->addWeek(),
            'monthly' => $date->addMonth(),
            default => $date->addMonths(12),
        };
    }

    /**
     * How many billing periods the latest payment covers. The one-time
     * domain fee is consumed by the very first payment only; everything
     * after that buys whole periods at the plan rate, capped at the
     * periods remaining in the plan so overpayments never overshoot.
     */
    public static function periodsCovered(PackageOrder $order): int
    {
        $planPeriods = max(1, (int) $order->periods);
        $remaining = max(1, $planPeriods - (int) $order->periods_covered);

        if ($order->billing_frequency === 'full') {
            return min(1, $remaining);
        }

        $rate = max(1, (int) $order->amount_per_period);
        $effective = max(0, (int) $order->paid_amount - ((int) $order->periods_covered === 0 ? (int) $order->domain_fee : 0));

        return min(max(1, (int) floor($effective / $rate)), $remaining);
    }

    public static function extendByPeriods(\DateTimeInterface $from, string $frequency, int $periods): CarbonInterface
    {
        $date = Carbon::instance($from);

        return match ($frequency) {
            'daily' => $date->addDays($periods),
            'weekly' => $date->addWeeks($periods),
            'monthly' => $date->addMonths($periods),
            default => $date->addMonths(12),
        };
    }

    /**
     * Called whenever an order payment is verified (manual or online).
     * The website status flips immediately; the service command is only
     * a best-effort follow-up for backends that acknowledge.
     */
    public static function recordPayment(PackageOrder $order): void
    {
        $periods = self::periodsCovered($order);
        $order->increment('periods_covered', $periods);

        $websites = $order->hosted_website_id
            ? HostedWebsite::where('id', $order->hosted_website_id)->get()
            : collect();

        foreach ($websites as $website) {
            $base = $website->next_renewal_at && $website->next_renewal_at->isFuture()
                ? $website->next_renewal_at
                : now();

            $website->forceFill([
                'next_renewal_at' => self::extendByPeriods($base, $order->billing_frequency, $periods),
            ])->save();

            HostingAuditLog::log('subscription.extended', [
                'actor_type' => 'system',
                'hosted_website_id' => $website->id,
                'client_id' => $website->user_id,
                'meta' => [
                    'reference' => $order->reference,
                    'frequency' => $order->billing_frequency,
                    'periods' => $periods,
                    'paid' => (int) $order->paid_amount,
                    'renewal' => $website->fresh()->next_renewal_at->toDateString(),
                ],
            ]);

            if ($website->status === HostedWebsite::STATUS_SUSPENDED
                && $website->suspension_reason === 'Overdue payment') {
                $website->forceFill([
                    'status' => HostedWebsite::STATUS_ACTIVE,
                    'suspension_reason' => null,
                ])->save();

                HostingAuditLog::log('restoration.requested', [
                    'actor_type' => 'system',
                    'hosted_website_id' => $website->id,
                    'client_id' => $website->user_id,
                    'meta' => ['reason' => 'auto: payment received via '.$order->reference],
                ]);

                try {
                    $command = ServiceCommander::build(
                        $website->fresh(), HostingCommand::ACTION_UNSUSPEND, 'system', null,
                        ['reason' => 'Balance cleared by '.$order->reference]
                    );
                    ProcessHostingCommand::dispatch($command->id);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    /**
     * Daily sweep: suspend active websites past renewal with money still
     * owed. The status flips immediately so expiry always takes effect,
     * even when no service backend acknowledges; the service command is a
     * best-effort follow-up and is never queued twice for one site.
     *
     * @return int number of websites suspended by this run
     */
    public static function suspendOverdue(): int
    {
        $suspended = 0;

        $sites = HostedWebsite::where('status', HostedWebsite::STATUS_ACTIVE)
            ->whereNotNull('next_renewal_at')
            ->where('next_renewal_at', '<', now())
            ->get();

        foreach ($sites as $website) {
            $owed = (int) $website->orders()->whereIn('status', ['pending', 'submitted'])
                ->sum('total_amount');

            if ($owed <= 0) {
                continue;
            }

            $website->forceFill([
                'status' => HostedWebsite::STATUS_SUSPENDED,
                'suspension_reason' => 'Overdue payment',
            ])->save();

            HostingAuditLog::log('suspension.requested', [
                'actor_type' => 'system',
                'hosted_website_id' => $website->id,
                'client_id' => $website->user_id,
                'meta' => ['reason' => 'auto: overdue UGX '.number_format($owed)],
            ]);

            $open = $website->commands()
                ->where('action', HostingCommand::ACTION_SUSPEND)
                ->whereIn('state', [HostingCommand::STATE_PENDING, HostingCommand::STATE_PROCESSING, HostingCommand::STATE_RETRY])
                ->exists();

            $recent = $website->commands()
                ->where('action', HostingCommand::ACTION_SUSPEND)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if (! $open && ! $recent) {
                try {
                    $command = ServiceCommander::build(
                        $website, HostingCommand::ACTION_SUSPEND, 'system', null,
                        ['reason' => 'Overdue payment']
                    );
                    ProcessHostingCommand::dispatch($command->id);
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            $suspended++;
        }

        return $suspended;
    }
}
