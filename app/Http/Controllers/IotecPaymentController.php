<?php

namespace App\Http\Controllers;

use App\Models\LaunchSignup;
use App\Models\PackageOrder;
use App\Notifications\SiteAlert;
use App\Services\Hosting\HostingSubscription;
use App\Services\IotecPay;
use App\Services\IotecPayException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class IotecPaymentController extends Controller
{
    /**
     * Start an ioTec Mobile Money prompt for an existing order.
     * The order itself is created first via CheckoutController@store.
     */
    public function payOrder(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $order = PackageOrder::where('reference', $data['reference'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['status' => 'paid', 'redirect' => route('checkout.success', $order)]);
        }

        try {
            IotecPay::assertEnabled();

            $tx = IotecPay::collectMoMo(
                $data['phone'],
                (int) $order->due_today,
                $order->reference,
                $request->user()->name,
                substr("ttryy {$order->package} — {$order->reference}", 0, 100),
                substr("{$order->business_name} · {$order->package}", 0, 100),
            );

            $order->update([
                'payment_method' => 'iotec',
                'gateway_tx_id' => $tx['id'],
                'phone' => $data['phone'],
            ]);
        } catch (IotecPayException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'tx_id' => $tx['id'],
            'status' => $tx['status'] ?? 'Pending',
            'amount' => $order->due_today,
        ]);
    }

    /** Poll the authoritative ioTec status for an order (backs the "approve on your phone" UI). */
    public function orderStatus(Request $request)
    {
        $data = $request->validate(['reference' => ['required', 'string']]);

        $order = PackageOrder::where('reference', $data['reference'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['status' => 'paid', 'redirect' => route('checkout.success', $order)]);
        }

        try {
            IotecPay::assertEnabled();

            $tx = $order->gateway_tx_id
                ? IotecPay::status($order->gateway_tx_id)
                : IotecPay::statusByExternalId($order->reference);
        } catch (IotecPayException $e) {
            return response()->json(['status' => 'unknown', 'message' => $e->getMessage()]);
        }

        if (IotecPay::isSuccess($tx)) {
            $this->finalizeOrder($order->fresh(), $tx);

            return response()->json(['status' => 'paid', 'redirect' => route('checkout.success', $order)]);
        }

        if (IotecPay::isFailed($tx)) {
            return response()->json(['status' => 'failed', 'message' => $tx['statusMessage'] ?? 'The payment was not approved. Try again or use a different number.']);
        }

        return response()->json(['status' => 'pending']);
    }

    /** Start an ioTec card collection and hand back the PegPay redirect URL. */
    public function payOrderCard(Request $request)
    {
        $data = $request->validate(['reference' => ['required', 'string']]);

        $order = PackageOrder::where('reference', $data['reference'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['redirect' => route('checkout.success', $order)]);
        }

        try {
            IotecPay::assertEnabled();

            $tx = IotecPay::collectCard(
                $request->user()->email,
                (int) $order->due_today,
                $order->reference,
                route('payments.iotec.card-return'),
                $request->user()->name,
                substr("ttryy {$order->package} — {$order->reference}", 0, 100),
            );

            $order->update(['payment_method' => 'iotec', 'gateway_tx_id' => $tx['id']]);
        } catch (IotecPayException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if (empty($tx['cardRedirectUrl'])) {
            Log::warning('ioTec card collect returned no redirect', ['tx' => $tx['id'] ?? null]);

            return response()->json(['message' => 'Card payments could not be started. Please pay with Mobile Money.'], 422);
        }

        return response()->json(['redirect' => $tx['cardRedirectUrl']]);
    }

    /**
     * Landing spot after PegPay. The query outcome is never trusted —
     * the transaction is re-read from ioTec before anything is finalized.
     */
    public function cardReturn(Request $request)
    {
        $id = (string) $request->query('id', '');

        if ($id === '' || ! IotecPay::enabled()) {
            return redirect()->route('checkout')->withErrors(['payment' => 'Card payment could not be confirmed.']);
        }

        try {
            $tx = IotecPay::status($id);
        } catch (IotecPayException) {
            return redirect()->route('checkout')->withErrors(['payment' => 'Card payment could not be confirmed yet — check My packages in a minute.']);
        }

        $order = PackageOrder::where('gateway_tx_id', $id)->first()
            ?? PackageOrder::where('reference', $tx['externalId'] ?? '')->first();

        if (! $order) {
            return redirect()->route('checkout')->withErrors(['payment' => 'Order not found for that payment.']);
        }

        abort_if($order->user_id !== $request->user()?->id && ! $request->user()?->is_admin, 403);

        if (IotecPay::isSuccess($tx)) {
            $this->finalizeOrder($order, $tx);
        }

        return redirect()->route('checkout.success', $order);
    }

    /**
     * Launch 10K instant pay: create the application and immediately
     * send the UGX 10,000 Mobile Money prompt. Guests welcome — no account needed.
     */
    public function payLaunch(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'idea_name' => ['required', 'string', 'max:120'],
            'niche' => ['nullable', 'string', 'max:80'],
            'idea' => ['required', 'string', 'min:20', 'max:3000'],
            'terms' => ['accepted'],
        ]);

        $fee = (int) config('packages.launch_fee', 10000);

        $signup = LaunchSignup::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'idea_name' => $validated['idea_name'],
            'niche' => $validated['niche'] ?? null,
            'idea' => $validated['idea'],
            'status' => LaunchSignup::STATUS_NEW,
            'payment_status' => 'pending',
        ]);

        $this->mailAdminAboutSignup($signup);

        try {
            IotecPay::assertEnabled();

            $tx = IotecPay::collectMoMo(
                $validated['phone'],
                $fee,
                'LAUNCH-'.$signup->id,
                $validated['name'],
                substr("ttryy Launch 10K — {$validated['idea_name']}", 0, 100),
                substr("LaunchSignup #{$signup->id}", 0, 100),
            );

            $signup->update(['gateway_tx_id' => $tx['id']]);
        } catch (IotecPayException $e) {
            return response()->json([
                'signup_id' => $signup->id,
                'message' => $e->getMessage().' Your application was still received — you can pay later.',
            ], 422);
        }

        return response()->json([
            'signup_id' => $signup->id,
            'tx_id' => $tx['id'],
            'status' => $tx['status'] ?? 'Pending',
            'amount' => $fee,
        ]);
    }

    /** Poll the authoritative ioTec status for a launch signup payment. */
    public function launchStatus(Request $request)
    {
        $data = $request->validate(['signup' => ['required', 'integer']]);

        $signup = LaunchSignup::findOrFail($data['signup']);

        if ($signup->payment_status === 'paid') {
            return response()->json(['status' => 'paid']);
        }

        try {
            IotecPay::assertEnabled();

            $tx = $signup->gateway_tx_id
                ? IotecPay::status($signup->gateway_tx_id)
                : IotecPay::statusByExternalId('LAUNCH-'.$signup->id);
        } catch (IotecPayException $e) {
            return response()->json(['status' => 'unknown', 'message' => $e->getMessage()]);
        }

        if (IotecPay::isSuccess($tx)) {
            $this->finalizeSignup($signup, $tx);

            return response()->json(['status' => 'paid']);
        }

        if (IotecPay::isFailed($tx)) {
            $signup->update(['payment_status' => 'failed']);

            return response()->json(['status' => 'failed', 'message' => $tx['statusMessage'] ?? 'The payment was not approved. Your application is still received — try again or pay later.']);
        }

        return response()->json(['status' => 'pending']);
    }

    /**
     * ioTec portal callback (Success / Failed / SentToVendor).
     * The payload is a hint only — the transaction is re-read from ioTec
     * before anything is marked paid.
     */
    public function callback(Request $request)
    {
        $expected = (string) config('services.iotec.callback_token', '');

        if ($expected !== '') {
            $given = (string) $request->header('X-Callback-Token', '');

            if ($given === '' && str_starts_with((string) $request->header('Authorization', ''), 'Bearer ')) {
                $given = substr((string) $request->header('Authorization'), 7);
            }

            if (! hash_equals($expected, $given)) {
                Log::warning('ioTec callback with bad token');

                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        $payload = $request->json()->all();
        $id = (string) ($payload['id'] ?? '');
        $externalId = (string) ($payload['externalId'] ?? '');

        if ($id === '' && $externalId === '') {
            return response()->json(['message' => 'Nothing to do.']);
        }

        try {
            $tx = $id !== '' ? IotecPay::status($id) : IotecPay::statusByExternalId($externalId);
        } catch (IotecPayException $e) {
            Log::warning('ioTec callback verify failed', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Verify later.'], 500);
        }

        $txId = (string) ($tx['id'] ?? $id);
        $txExternal = (string) ($tx['externalId'] ?? $externalId);

        $order = ($txId !== '' ? PackageOrder::where('gateway_tx_id', $txId)->first() : null)
            ?? ($txExternal !== '' ? PackageOrder::where('reference', $txExternal)->first() : null);

        if ($order && IotecPay::isSuccess($tx)) {
            $this->finalizeOrder($order, $tx);
        }

        $signup = ($txId !== '' ? LaunchSignup::where('gateway_tx_id', $txId)->first() : null)
            ?? ($txExternal !== '' && str_starts_with($txExternal, 'LAUNCH-')
                ? LaunchSignup::find((int) substr($txExternal, 7))
                : null);

        if ($signup && IotecPay::isSuccess($tx)) {
            $this->finalizeSignup($signup, $tx);
        } elseif ($signup && IotecPay::isFailed($tx)) {
            $signup->update(['payment_status' => 'failed']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Mark an order paid from an authoritative ioTec transaction.
     * Idempotent — repeat callbacks and polls are safe.
     */
    protected function finalizeOrder(PackageOrder $order, array $tx): void
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

    /** Idempotent launch-fee finalization. */
    protected function finalizeSignup(LaunchSignup $signup, array $tx): void
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

    protected function mailAdminAboutSignup(LaunchSignup $signup): void
    {
        try {
            Mail::raw(
                "New Launch 10K application #{$signup->id} (instant-pay started):\n\n".
                "Name: {$signup->name}\nPhone: {$signup->phone}\nEmail: ".($signup->email ?? '—')."\n".
                "Idea: {$signup->idea_name}\nNiche: ".($signup->niche ?? '—')."\n\n{$signup->idea}",
                function ($message) {
                    $message->to(config('packages.admin_email'))->subject('New Launch 10K application');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Launch signup email failed', ['error' => $e->getMessage()]);
        }
    }
}
