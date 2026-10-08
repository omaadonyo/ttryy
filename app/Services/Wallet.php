<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;

class Wallet
{
    public static function balance(User $user): int
    {
        return (int) $user->token_balance;
    }

    public static function canAfford(User $user, int $amount): bool
    {
        return self::balance($user) >= $amount;
    }

    public static function credit(User $user, int $amount, string $description): void
    {
        $user->increment('token_balance', $amount);
        $user->refresh();

        $user->tokenTransactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'balance_after' => (int) $user->token_balance,
            'description' => $description,
        ]);
    }

    /**
     * @throws RuntimeException when the balance is insufficient.
     */
    public static function debit(User $user, int $amount, string $description): void
    {
        if (! self::canAfford($user, $amount)) {
            throw new RuntimeException('Insufficient token balance.');
        }

        $user->decrement('token_balance', $amount);
        $user->refresh();

        $user->tokenTransactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'balance_after' => (int) $user->token_balance,
            'description' => $description,
        ]);
    }
}
