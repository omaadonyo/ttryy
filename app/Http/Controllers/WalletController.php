<?php

namespace App\Http\Controllers;

use App\Models\TokenTopup;
use App\Models\WalletTopup;
use App\Notifications\SiteAlert;
use App\Services\CashWallet;
use App\Services\Flutterwave;
use App\Services\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        return view('wallet', [
            'balance' => (int) $user->token_balance,
            'cashBalance' => (int) $user->balance_ugx,
            'cashLedger' => $user->walletTransactions()->latest()->take(15)->get(),
            'cashTopups' => $user->walletTopups()->latest()->take(10)->get(),
            'packs' => config('tokens.packs'),
            'costs' => config('tokens.costs'),
            'topups' => $user->tokenTopups()->latest()->take(10)->get(),
            'ledger' => $user->tokenTransactions()->latest()->take(15)->get(),
            'flwKey' => config('services.flutterwave.public_key'),
            'usage30' => [
                'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->all(),
                'used' => $days->map(fn ($d) => (int) abs($user->tokenTransactions()->where('type', 'debit')->whereDate('created_at', $d)->sum('amount')))->all(),
                'earned' => $days->map(fn ($d) => (int) $user->tokenTransactions()->where('type', 'credit')->whereDate('created_at', $d)->sum('amount'))->all(),
            ],
            'spentTotal' => (int) abs($user->tokenTransactions()->where('type', 'debit')->sum('amount')),
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

    /**
     * Start a cash-wallet top-up. The customer chooses any amount; the
     * credit lands once Flutterwave confirms it.
     */
    public function topupCash(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1000', 'max:10000000'],
        ]);

        do {
            $reference = 'TTW-'.strtoupper(Str::random(6));
        } while (WalletTopup::where('reference', $reference)->exists());

        $topup = WalletTopup::create([
            'user_id' => $request->user()->id,
            'reference' => $reference,
            'amount_ugx' => (int) $validated['amount'],
            'method' => 'flutterwave',
            'status' => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'reference' => $topup->reference,
                'amount_ugx' => $topup->amount_ugx,
                'currency' => 'UGX',
                'email' => $request->user()->email,
                'name' => $request->user()->name,
                'public_key' => config('services.flutterwave.public_key'),
            ]);
        }

        return redirect()->route('wallet.index');
    }

    public function verifyCash(Request $request)
    {
        $data = $request->validate([
            'transaction_id' => ['required'],
            'tx_ref' => ['required', 'string'],
        ]);

        $reference = preg_match('/^(TTW-[A-Z0-9]{6})-\d+$/', $data['tx_ref'], $m)
            ? $m[1]
            : $data['tx_ref'];

        $topup = WalletTopup::where('reference', $reference)
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

        CashWallet::credit(
            $request->user(),
            $topup->amount_ugx,
            "Wallet top-up {$topup->reference}",
            'topup',
            $topup->reference
        );

        $request->user()->notify(new SiteAlert(
            'Wallet topped up',
            'UGX '.number_format($topup->amount_ugx).' added to your account balance.',
            route('wallet.index'),
            'payment'
        ));

        return response()->json(['redirect' => route('wallet.index')]);
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
            $request->user()->notify(new SiteAlert(
                'Top-up confirmed',
                '+'.number_format($topup->tokens).' tokens added to your wallet.',
                route('wallet.index'),
                'tokens'
            ));

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

        Wallet::credit(
            $request->user(),
            $topup->tokens,
            "Token top-up {$topup->reference} ({$topup->pack} pack)"
        );

        return response()->json(['redirect' => route('wallet.index')]);
    }
}
