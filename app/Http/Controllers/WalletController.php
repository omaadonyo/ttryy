<?php

namespace App\Http\Controllers;

use App\Models\TokenTopup;
use App\Services\Flutterwave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        return view('wallet', [
            'balance' => (int) $request->user()->token_balance,
            'packs' => config('tokens.packs'),
            'costs' => config('tokens.costs'),
            'topups' => $request->user()->tokenTopups()->latest()->take(10)->get(),
            'ledger' => $request->user()->tokenTransactions()->latest()->take(15)->get(),
            'flwKey' => config('services.flutterwave.public_key'),
        ]);
    }

    public function topup(Request $request)
    {
        $validated = $request->validate([
            'pack' => ['required', Rule::in(array_keys(config('tokens.packs')))],
        ]);

        $pack = config('tokens.packs')[$validated['pack']];

        do {
            $reference = 'TTP-'.strtoupper(Str::random(6));
        } while (TokenTopup::where('reference', $reference)->exists());

        $topup = TokenTopup::create([
            'user_id' => $request->user()->id,
            'reference' => $reference,
            'pack' => $validated['pack'],
            'tokens' => $pack['tokens'],
            'amount_ugx' => $pack['price'],
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'reference' => $topup->reference,
                'amount_ugx' => $topup->amount_ugx,
                'tokens' => $topup->tokens,
                'currency' => 'UGX',
                'email' => $request->user()->email,
                'name' => $request->user()->name,
                'public_key' => config('services.flutterwave.public_key'),
            ]);
        }

        return redirect()->route('wallet.index');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => ['required'],
            'tx_ref' => ['required', 'string'],
        ]);

        // tx_ref carries a per-attempt timestamp suffix (TTP-XXXXXX-<ms>).
        $reference = preg_match('/^(TTP-[A-Z0-9]{6})-\d+$/', $data['tx_ref'], $m)
            ? $m[1]
            : $data['tx_ref'];

        $topup = TokenTopup::where('reference', $reference)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($topup->status === 'paid') {
            return response()->json(['redirect' => route('wallet.index')]);
        }

        if (! config('services.flutterwave.secret_key')) {
            return response()->json(['message' => 'Online verification is not configured yet.'], 422);
        }

        $tx = Flutterwave::verifyTransaction($data['transaction_id']);

        if (! $tx
            || ($tx['status'] ?? null) !== 'successful'
            || ($tx['currency'] ?? null) !== 'UGX'
            || (int) ($tx['amount'] ?? 0) < $topup->amount_ugx) {
            return response()->json(['message' => 'Payment could not be verified.'], 422);
        }

        $topup->update([
            'status' => 'paid',
            'tx_ref' => (string) ($tx['id'] ?? $data['transaction_id']),
            'paid_at' => now(),
        ]);

        \App\Services\Wallet::credit(
            $request->user(),
            $topup->tokens,
            "Token top-up {$topup->reference} ({$topup->pack} pack)"
        );

        return response()->json(['redirect' => route('wallet.index')]);
    }
}
