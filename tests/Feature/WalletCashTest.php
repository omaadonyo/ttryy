<?php

use App\Models\User;
use App\Models\WalletTopup;
use App\Services\CashWallet;
use Illuminate\Support\Facades\Http;

test('customers can start a wallet top-up', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('wallet.topup-cash'), ['amount' => 25000])
        ->assertOk()
        ->assertJsonPath('amount_ugx', 25000)
        ->assertJsonPath('currency', 'UGX');

    expect(WalletTopup::where('user_id', $user->id)->where('status', 'pending')->exists())->toBeTrue();
});

test('wallet top-ups are rejected below the minimum', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('wallet.topup-cash'), ['amount' => 500])
        ->assertUnprocessable();
});

test('a verified top-up credits the account balance and ledger', function () {
    config()->set('services.flutterwave.secret_key', 'test-secret');

    Http::fake([
        'api.flutterwave.com/*' => Http::response([
            'data' => ['id' => 555, 'status' => 'successful', 'currency' => 'UGX', 'amount' => 25000],
        ], 200),
    ]);

    $user = User::factory()->create();
    $topup = WalletTopup::create([
        'user_id' => $user->id,
        'reference' => 'TTW-ABC123',
        'amount_ugx' => 25000,
        'method' => 'flutterwave',
        'status' => 'pending',
    ]);

    $this->actingAs($user)->postJson(route('wallet.verify-cash'), [
        'transaction_id' => 555,
        'tx_ref' => 'TTW-ABC123-1700000000',
    ])->assertOk();

    expect($user->fresh()->balance_ugx)->toBe(25000)
        ->and($topup->fresh()->status)->toBe('paid')
        ->and($user->walletTransactions()->where('type', 'credit')->sum('amount'))->toBe(25000);
});

test('the wallet page shows the cash balance and ledger', function () {
    $user = User::factory()->create();
    CashWallet::credit($user, 40000, 'Manual credit', 'adjustment');

    $this->actingAs($user)->get(route('wallet.index'))
        ->assertOk()
        ->assertSee('Account balance', false)
        ->assertSee('UGX 40,000', false);
});

test('the cash wallet refuses to go negative', function () {
    $user = User::factory()->create();

    expect(fn () => CashWallet::debit($user, 1000, 'test'))
        ->toThrow(RuntimeException::class);

    expect($user->fresh()->balance_ugx)->toBe(0);
});
