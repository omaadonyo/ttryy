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
     * Called whenever an order payment is verified (manual or online).
     */
    public static function recordPayment(PackageOrder $order): void
    {
        $websites = $order->hosted_website_id
            ? HostedWebsite::where('id', $order->hosted_website_id)->get()
            : collect();

        foreach ($websites as $website) {
            $base = $website->next_renewal_at && $website->next_renewal_at->isFuture()
                ? $website->next_renewal_at
                : now();

            $website->forceFill([
                'next_renewal_at' => self::periodEnd($base, $order->billing_frequency),
            ])->save();

            HostingAuditLog::log('subscription.extended', [
                'actor_type' => 'system',
                'hosted_website_id' => $website->id,
                'client_id' => $website->user_id,
                'meta' => [
                    'reference' => $order->reference,
                    'frequency' => $order->billing_frequency,
                    'renewal' => $website->fresh()->next_renewal_at->toDateString(),
                ],
            ]);

            if ($website->status === HostedWebsite::STATUS_SUSPENDED
                && $website->suspension_reason === 'Overdue payment') {
                $command = ServiceCommander::build(
                    $website, HostingCommand::ACTION_UNSUSPEND, 'system', null,
                    ['reason' => 'Balance cleared by '.$order->reference]
                );

                HostingAuditLog::log('restoration.requested', [
                    'actor_type' => 'system',
                    'hosted_website_id' => $website->id,
                    'client_id' => $website->user_id,
                    'operation_id' => $command->uuid,
                    'meta' => ['reason' => 'auto: payment received'],
                ]);

                ProcessHostingCommand::dispatch($command->id);
            }
        }
    }

    /**
     * Daily sweep: queue suspension for active websites past renewal with
     * money still owed. Skips sites that already have an open suspend
     * operation so runs never stack duplicates.
     *
     * @return int number of operations queued
     */
    public static function suspendOverdue(): int
    {
        $queued = 0;

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

            $open = $website->commands()
                ->where('action', HostingCommand::ACTION_SUSPEND)
                ->whereIn('state', [HostingCommand::STATE_PENDING, HostingCommand::STATE_PROCESSING, HostingCommand::STATE_RETRY])
                ->exists();

            $recent = $website->commands()
                ->where('action', HostingCommand::ACTION_SUSPEND)
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if ($open || $recent) {
                continue;
            }

            $command = ServiceCommander::build(
                $website, HostingCommand::ACTION_SUSPEND, 'system', null,
                ['reason' => 'Overdue payment']
            );

            HostingAuditLog::log('suspension.requested', [
                'actor_type' => 'system',
                'hosted_website_id' => $website->id,
                'client_id' => $website->user_id,
                'operation_id' => $command->uuid,
                'meta' => ['reason' => 'auto: overdue UGX '.number_format($owed)],
            ]);

            ProcessHostingCommand::dispatch($command->id);
            $queued++;
        }

        return $queued;
    }
}
