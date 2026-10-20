<?php

namespace App\Http\Controllers;

use App\Models\PackageOrder;
use App\Services\CashWallet;
use App\Services\IotecPay;
use App\Services\IotecPayException;
use App\Services\PackagePayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Lets a signed-in customer pay toward an existing order — either from
 * their cash wallet balance or with a Mobile Money prompt — without going
 * back through checkout.
 */
class OrderPaymentController extends Controller
{
    public function store(Request $request, PackageOrder $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $order->refresh();
        $remaining = max(0, (int) $order->total_amount - (int) $order->paid_amount);

        if ($remaining <= 0) {
            return $this->respond($request, ['message' => 'This order is already fully paid.'], 422);
        }

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:'.$remaining],
            'method' => ['required', Rule::in(['wallet', 'momo'])],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $amount = (int) $data['amount'];

        if ($data['method'] === 'wallet') {
            if (! CashWallet::canAfford($user, $amount)) {
                return $this->respond($request, ['message' => 'Not enough wallet balance — top up first.'], 422);
            }

            $payment = DB::transaction(function () use ($order, $user, $amount) {
                $payment = $order->payments()->create([
                    'user_id' => $user->id,
                    'reference' => PackagePayments::generateReference('PAY'),
                    'amount' => $amount,
                    'method' => 'wallet',
                    'status' => 'pending',
                ]);

                try {
                    CashWallet::debit($user, $amount, "Payment for order {$order->reference}", 'order', $order->reference);
                } catch (RuntimeException $e) {
                    throw $e;
                }

                PackagePayments::finalize($payment->fresh(), $amount);

                return $payment;
            });

            return $this->respond($request, [
                'status' => 'paid',
                'amount' => $payment->amount,
                'redirect' => route('packages.index'),
            ]);
        }

        // Mobile Money via ioTec.
        if (! IotecPay::enabled()) {
            return $this->respond($request, ['message' => 'Online Mobile Money is not available right now. Try the wallet or contact us on WhatsApp.'], 422);
        }

        $phone = trim((string) ($data['phone'] ?? ''));

        if ($phone === '') {
            return $this->respond($request, ['message' => 'Enter the Mobile Money number that will approve this payment.'], 422);
        }

        $payment = $order->payments()->create([
            'user_id' => $user->id,
            'reference' => PackagePayments::generateReference('PAY'),
            'amount' => $amount,
            'method' => 'momo',
            'status' => 'pending',
        ]);

        try {
            $tx = IotecPay::collectMoMo(
                $phone,
                $amount,
                $payment->reference,
                $user->name,
                substr("ttryy {$order->package} — {$order->reference}", 0, 100),
                substr("Order {$order->reference} payment", 0, 100),
            );

            $payment->update(['gateway_tx_id' => $tx['id']]);
        } catch (IotecPayException $e) {
            $payment->update(['status' => 'failed']);

            return $this->respond($request, ['message' => $e->getMessage()], 422);
        }

        return $this->respond($request, [
            'status' => 'pending',
            'tx_id' => $tx['id'],
            'amount' => $amount,
            'reference' => $payment->reference,
        ]);
    }

    public function status(Request $request, PackageOrder $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $order->refresh();

        if ($order->status === 'paid' || (int) $order->paid_amount >= (int) $order->total_amount) {
            return response()->json(['status' => 'paid', 'redirect' => route('packages.index')]);
        }

        $payment = $order->payments()->where('status', 'pending')->latest()->first();

        if (! $payment) {
            return response()->json(['status' => 'none']);
        }

        if (! IotecPay::enabled()) {
            return response()->json(['status' => 'unknown']);
        }

        try {
            $tx = $payment->gateway_tx_id
                ? IotecPay::status($payment->gateway_tx_id)
                : IotecPay::statusByExternalId($payment->reference);
        } catch (IotecPayException $e) {
            return response()->json(['status' => 'unknown', 'message' => $e->getMessage()]);
        }

        if (IotecPay::isSuccess($tx)) {
            PackagePayments::finalize($payment, (int) ($tx['amount'] ?? $payment->amount));

            return response()->json(['status' => 'paid', 'redirect' => route('packages.index')]);
        }

        if (IotecPay::isFailed($tx)) {
            $payment->update(['status' => 'failed']);

            return response()->json(['status' => 'failed', 'message' => $tx['statusMessage'] ?? 'The payment was not approved.']);
        }

        return response()->json(['status' => 'pending']);
    }

    private function respond(Request $request, array $payload, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json($payload, $status);
        }

        return $status >= 400
            ? back()->withErrors(['payment' => $payload['message'] ?? 'Payment failed.'])->withInput()
            : back()->with('status', $payload['message'] ?? 'Payment received.');
    }
}
