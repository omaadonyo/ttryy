<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('t/{token}', [MarketingController::class, 'trackOpen'])->name('tracking.open');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('orders', [AdminController::class, 'orders'])->name('orders');
    Route::patch('orders/{order}/paid', [AdminController::class, 'markPaid'])->name('orders.paid');
    Route::patch('orders/{order}/handover', [AdminController::class, 'markHandedOver'])->name('orders.handover');
    Route::get('payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::patch('users/{user}/admin', [AdminController::class, 'toggleAdmin'])->name('users.admin');
    Route::get('packages', [AdminController::class, 'packages'])->name('packages');
});

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('orders/new', [CheckoutController::class, 'index'])->name('orders.new');
    Route::get('payments', [DashboardController::class, 'payments'])->name('payments.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('checkout/verify', [CheckoutController::class, 'verify'])->name('checkout.verify');
    Route::get('orders/{order}/invoice', [CheckoutController::class, 'invoice'])->name('orders.invoice');
    Route::get('checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('packages', [CheckoutController::class, 'orders'])->name('packages.index');
    Route::get('scraper', [DashboardController::class, 'scraper'])->name('scraper.index');
    Route::get('prospects', [DashboardController::class, 'prospects'])->name('prospects.index');
    Route::post('scraper/save', [DashboardController::class, 'storeContacts'])->name('scraper.save');
    Route::get('contacts', [DashboardController::class, 'contacts'])->name('contacts.index');
    Route::delete('contacts/{contact}', [DashboardController::class, 'destroyContact'])->name('contacts.destroy');
    Route::get('contacts/export', [DashboardController::class, 'exportContacts'])->name('contacts.export');
    Route::get('marketing', [MarketingController::class, 'index'])->name('marketing.index');
    Route::post('marketing/generate', [MarketingController::class, 'generate'])->name('marketing.generate');
    Route::post('marketing/templates', [MarketingController::class, 'storeTemplate'])->name('marketing.templates.store');
    Route::delete('marketing/templates/{template}', [MarketingController::class, 'destroyTemplate'])->name('marketing.templates.destroy');
    Route::post('marketing/campaigns', [MarketingController::class, 'sendCampaign'])->name('marketing.campaigns.send');
    Route::get('marketing/campaigns/{campaign}', [MarketingController::class, 'showCampaign'])->name('marketing.campaigns.show');
    Route::post('marketing/groups/suggest', [MarketingController::class, 'suggestGroup'])->name('marketing.groups.suggest');
    Route::post('scraper/search', [MarketingController::class, 'scraperSearch'])->name('scraper.search');
    Route::post('groups/{group}/unlock', [MarketingController::class, 'unlockGroup'])->name('groups.unlock');
    Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');
    Route::post('wallet/verify', [WalletController::class, 'verify'])->name('wallet.verify');
});

// Guests may browse checkout and sign in inline; placing an order needs an account.
Route::get('dashboard/checkout', [CheckoutController::class, 'index'])->name('checkout');

require __DIR__.'/settings.php';
