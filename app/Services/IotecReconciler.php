<?php

namespace App\Services;

use App\Models\LaunchSignup;
use App\Models\OrderPayment;
use App\Models\PackageOrder;
use App\Notifications\SiteAlert;
use App\Services\Hosting\HostingSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Finalizes and reconciles ioTec Pay collections.
 *
 * Webhooks and the polling UI both finalize in real time; this service is
 * also the safety net: a scheduled sweep re-reads the authoritative ioTec
 * status for anything still pending, so a lost callback never leaves a
 * paying customer stuck on "awaiting payment".
 */
class IotecReconciler
{
    /**
     * Mark an order paid from an authoritative ioTec transaction.
     * Idempotent — repeat callbacks and polls are safe.
     */
    public static function finalizeOrder(PackageOrder $order, array $tx): void
    {
        if ($order->status === 'paid') {
            return;
        }

        $order->update([
            'status' => 'paid',
            'paid_amount' => (int) ($tx['amount'] ?? $order->due_today),
            'paid_at' => now(),
            'tx_ref' => (string) ($tx['id'] ?? $order->gateway_tx_id ?? ''),
            'gateway_tx_id' => (string) ($tx['id'] ?? $order->gateway_tx_id ?? ''),
            'payment_method' => 'iotec',
        ]);

        HostingSubscription::recordPayment($order->fresh(), (int) ($tx['amount'] ?? $order->due_today));

        if ($order->user) {
            $order->user->notify(new SiteAlert(
                "Payment received — {$order->reference}",
                'UGX '.number_format((int) $order->paid_amount).' confirmed. We start work immediately.',
                route('checkout.success', $order),
                'payment'
            ));
        }
    }

    /**
     * Mark a self-service order payment paid from an authoritative ioTec
     * transaction and credit the order. Idempotent.
     */
    public static function finalizeOrderPayment(OrderPayment $payment, array $tx): void
    {
        if ($payment->status === 'paid') {
            return;
        }

        $payment->update([
            'gateway_tx_id' => (string) ($tx['id'] ?? $payment->gateway_tx_id ?? ''),
        ]);

        PackagePayments::finalize($payment->fresh(), (int) ($tx['amount'] ?? $payment->amount));
    }

    /** Idempotent launch-fee finalization. */
    public static function finalizeSignup(LaunchSignup $signup, array $tx): void
    {
        if ($signup->payment_status === 'paid') {
            return;
        }

        $signup->update([
            'payment_status' => 'paid',
            'paid_amount' => (int) ($tx['amount'] ?? config('packages.launch_fee', 10000)),
            'paid_at' => now(),
            'gateway_tx_id' => (string) ($tx['id'] ?? $signup->gateway_tx_id ?? ''),
        ]);

        try {
            Mail::raw(
                "Launch 10K fee PAID — application #{$signup->id} ({$signup->idea_name}, {$signup->name}, {$signup->phone}).",
                fn ($message) => $message->to(config('packages.admin_email'))->subject('Launch 10K fee paid')
            );
        } catch (\Throwable $e) {
            Log::warning('Launch paid email failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Re-read ioTec status for orders still awaiting payment and finalize
     * any that actually succeeded. Nothing is ever marked paid from a
     * local guess — the provider is always asked first.
     *
     * @return array{checked:int, paid:int, failed:int}
     */
    public static function reconcileOrders(int $olderThanMinutes = 3): array
    {
        $result = ['checked' => 0, 'paid' => 0, 'failed' => 0];

        if (! IotecPay::enabled()) {
            return $result;
        }

        $orders = PackageOrder::whereIn('status', ['pending', 'submitted'])
            ->whereNotNull('gateway_tx_id')
            ->where('updated_at', '<=', now()->subMinutes(max(1, $olderThanMinutes)))
            ->get();

        foreach ($orders as $order) {
            try {
                $tx = IotecPay::status($order->gateway_tx_id);
            } catch (\Throwable $e) {
                Log::warning('ioTec reconcile lookup failed', ['order' => $order->reference, 'error' => $e->getMessage()]);

                continue;
            }

            $result['checked']++;

            if (IotecPay::isSuccess($tx)) {
                self::finalizeOrder($order, $tx);
                $result['paid']++;
            } elseif (IotecPay::isFailed($tx)) {
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * Re-read ioTec status for self-service order payments still pending and
     * finalize any that actually succeeded.
     *
     * @return array{checked:int, paid:int, failed:int}
     */
    public static function reconcileOrderPayments(int $olderThanMinutes = 3): array
    {
        $result = ['checked' => 0, 'paid' => 0, 'failed' => 0];

        if (! IotecPay::enabled()) {
            return $result;
        }

        $payments = OrderPayment::where('status', 'pending')
            ->where('method', 'momo')
            ->whereNotNull('gateway_tx_id')
            ->where('updated_at', '<=', now()->subMinutes(max(1, $olderThanMinutes)))
            ->get();

        foreach ($payments as $payment) {
            try {
                $tx = IotecPay::status($payment->gateway_tx_id);
            } catch (\Throwable $e) {
                Log::warning('ioTec reconcile lookup failed', ['payment' => $payment->reference, 'error' => $e->getMessage()]);

                continue;
            }

            $result['checked']++;

            if (IotecPay::isSuccess($tx)) {
                self::finalizeOrderPayment($payment, $tx);
                $result['paid']++;
            } elseif (IotecPay::isFailed($tx)) {
                $payment->update(['status' => 'failed']);
                $result['failed']++;
            }
        }

        return $result;
    }

    /**
     * Re-read ioTec status for launch-fee payments still pending.
     *
     * @return array{checked:int, paid:int, failed:int}
     */
    public static function reconcileSignups(int $olderThanMinutes = 3): array
    {
        $result = ['checked' => 0, 'paid' => 0, 'failed' => 0];

        if (! IotecPay::enabled()) {
            return $result;
        }

        $signups = LaunchSignup::where('payment_status', 'pending')
            ->whereNotNull('gateway_tx_id')
            ->where('updated_at', '<=', now()->subMinutes(max(1, $olderThanMinutes)))
            ->get();

        foreach ($signups as $signup) {
            try {
                $tx = IotecPay::status($signup->gateway_tx_id);
            } catch (\Throwable $e) {
                Log::warning('ioTec reconcile lookup failed', ['signup' => $signup->id, 'error' => $e->getMessage()]);

                continue;
            }

            $result['checked']++;

            if (IotecPay::isSuccess($tx)) {
                self::finalizeSignup($signup, $tx);
                $result['paid']++;
            } elseif (IotecPay::isFailed($tx)) {
                $result['failed']++;
            }
        }

        return $result;
    }
}
