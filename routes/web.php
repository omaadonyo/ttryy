<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('checkout/verify', [CheckoutController::class, 'verify'])->name('checkout.verify');
    Route::get('orders/{order}/invoice', [CheckoutController::class, 'invoice'])->name('orders.invoice');
    Route::get('checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('packages', [CheckoutController::class, 'orders'])->name('packages.index');
    Route::get('scraper', [DashboardController::class, 'scraper'])->name('scraper.index');
});

// Guests may browse checkout and sign in inline; placing an order needs an account.
Route::get('dashboard/checkout', [CheckoutController::class, 'index'])->name('checkout');

require __DIR__.'/settings.php';
