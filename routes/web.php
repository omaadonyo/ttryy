<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('orders', [AdminController::class, 'orders'])->name('orders');
    Route::patch('orders/{order}/paid', [AdminController::class, 'markPaid'])->name('orders.paid');
    Route::get('payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::patch('users/{user}/admin', [AdminController::class, 'toggleAdmin'])->name('users.admin');
    Route::get('packages', [AdminController::class, 'packages'])->name('packages');
});

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
