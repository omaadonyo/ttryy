<?php

namespace App\Services;

use App\Models\OrderPayment;
use App\Notifications\SiteAlert;
use App\Services\Hosting\HostingSubscription;
use Illuminate\Support\Str;

/**
 * Applies a self-service payment (wallet or Mobile Money) to an order.
 *
 * Credits accumulate on `paid_amount`; the order flips to paid once the
 * total is covered, and the hosting subscription renews pro-rata. Safe to
 * call more than once — repeat callbacks are ignored.
 */
class PackagePayments
{
    public static function finalize(OrderPayment $payment, ?int $amount = null): void
    {
        if ($payment->status === 'paid') {
            return;
        }

        $order = $payment->order;
        $credited = max(1, $amount ?? (int) $payment->amount);
        $credited = min($credited, max(0, (int) $order->total_amount - (int) $order->paid_amount));

        if ($credited <= 0) {
            $payment->update(['status' => 'paid', 'paid_at' => now(), 'amount' => 0]);

            return;
        }

        $newPaid = (int) $order->paid_amount + $credited;

        $order->update([
            'paid_amount' => min((int) $order->total_amount, $newPaid),
            'paid_at' => now(),
            'status' => $newPaid >= (int) $order->total_amount
                ? 'paid'
                : ($order->status === 'pending' ? 'submitted' : $order->status),
        ]);

        $payment->update(['status' => 'paid', 'paid_at' => now(), 'amount' => $credited]);

        HostingSubscription::recordPayment($order->fresh(), $credited);

        if ($order->user) {
            $left = max(0, (int) $order->fresh()->total_amount - (int) $order->fresh()->paid_amount);

            $order->user->notify(new SiteAlert(
                "Payment received — {$order->reference}",
                'UGX '.number_format($credited).' received'.($left > 0
                    ? '. UGX '.number_format($left).' still to pay.'
                    : ' — fully paid. Thank you.'),
                route('packages.index'),
                'payment'
            ));
        }
    }

    public static function generateReference(string $prefix = 'PAY'): string
    {
        do {
            $reference = $prefix.'-'.strtoupper(Str::random(8));
        } while (OrderPayment::where('reference', $reference)->exists());

        return $reference;
    }
}
