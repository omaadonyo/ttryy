<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Flutterwave
{
    /**
     * Verify a transaction with Flutterwave. Returns the transaction
     * payload array on any completed lookup, or null when unreachable.
     */
    public static function verifyTransaction(string|int $transactionId): ?array
    {
        $secret = config('services.flutterwave.secret_key');

        if (! $secret) {
            return null;
        }

        $response = Http::withToken($secret)->acceptJson()->get(
            'https://api.flutterwave.com/v3/transactions/'.$transactionId.'/verify'
        );

        if (! $response->successful()) {
            return null;
        }

        return $response->json('data');
    }

    public static function isConfigured(): bool
    {
        return (bool) config('services.flutterwave.public_key');
    }
}
