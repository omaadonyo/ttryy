<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use RuntimeException;

/**
 * The cash wallet: a UGX account balance kept separate from tokens.
 *
 * Customers top it up with Mobile Money or card, and it is debited to pay
 * subscriptions. Every movement is written to the wallet ledger so the
 * balance is always reproducible.
 */
class CashWallet
{
    public static function balance(User $user): int
    {
        return (int) $user->balance_ugx;
    }

    public static function canAfford(User $user, int $amount): bool
    {
        return $amount > 0 && self::balance($user) >= $amount;
    }

    public static function credit(User $user, int $amount, string $description, string $source = 'topup', ?string $reference = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Credit amount must be positive.');
        }

        $user->increment('balance_ugx', $amount);
        $user->refresh();

        return $user->walletTransactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'balance_after' => (int) $user->balance_ugx,
            'source' => $source,
            'reference' => $reference,
            'description' => $description,
        ]);
    }

    /**
     * @throws RuntimeException when the balance is insufficient.
     */
    public static function debit(User $user, int $amount, string $description, string $source = 'order', ?string $reference = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Debit amount must be positive.');
        }

        if (! self::canAfford($user, $amount)) {
            throw new RuntimeException('Insufficient wallet balance.');
        }

        $user->decrement('balance_ugx', $amount);
        $user->refresh();

        return $user->walletTransactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'balance_after' => (int) $user->balance_ugx,
            'source' => $source,
            'reference' => $reference,
            'description' => $description,
        ]);
    }
}
