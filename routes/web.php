<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminHostingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\LaunchController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('launch', [LaunchController::class, 'index'])->name('launch');
Route::post('launch', [LaunchController::class, 'store'])->name('launch.store');

Route::view('privacy', 'privacy')->name('privacy');
Route::view('terms', 'terms')->name('terms');
Route::view('sitemap', 'sitemap')->name('sitemap.page');
Route::get('sitemap.xml', function () {
    $today = now()->toDateString();
    $urls = [
        ['loc' => route('home'), 'lastmod' => $today, 'freq' => 'daily', 'priority' => '1.0'],
        ['loc' => route('checkout'), 'lastmod' => $today, 'freq' => 'weekly', 'priority' => '0.8'],
        ['loc' => route('privacy'), 'lastmod' => $today, 'freq' => 'monthly', 'priority' => '0.3'],
        ['loc' => route('terms'), 'lastmod' => $today, 'freq' => 'monthly', 'priority' => '0.3'],
        ['loc' => route('sitemap.page'), 'lastmod' => $today, 'freq' => 'weekly', 'priority' => '0.4'],
        ['loc' => route('login'), 'lastmod' => $today, 'freq' => 'monthly', 'priority' => '0.2'],
        ['loc' => route('register'), 'lastmod' => $today, 'freq' => 'monthly', 'priority' => '0.2'],
    ];

    return response()->view('sitemap-xml', ['urls' => $urls])->header('Content-Type', 'text/xml');
})->name('sitemap.xml');

Route::get('t/{token}', [MarketingController::class, 'trackOpen'])->name('tracking.open');
Route::get('t/w/{uuid}', [HostingController::class, 'beacon'])->middleware('throttle:120,1')->name('hosting.beacon');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('orders', [AdminController::class, 'orders'])->name('orders');
    Route::patch('orders/{order}/paid', [AdminController::class, 'markPaid'])->name('orders.paid');
    Route::patch('orders/{order}/handover', [AdminController::class, 'markHandedOver'])->name('orders.handover');
    Route::get('payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::patch('users/{user}/admin', [AdminController::class, 'toggleAdmin'])->name('users.admin');
    Route::get('packages', [AdminController::class, 'packages'])->name('packages');
    Route::get('hosting', [AdminHostingController::class, 'dashboard'])->name('hosting.dashboard');
    Route::get('hosting/websites', [AdminHostingController::class, 'websites'])->name('hosting.websites');
    Route::get('hosting/websites/{website}', [AdminHostingController::class, 'websiteShow'])->name('hosting.websites.show');
    Route::post('hosting/websites/{website}/suspend', [AdminHostingController::class, 'suspend'])->name('hosting.websites.suspend');
    Route::post('hosting/websites/{website}/restore', [AdminHostingController::class, 'restore'])->name('hosting.websites.restore');
    Route::post('hosting/websites/{website}/health-check', [AdminHostingController::class, 'triggerHealthCheck'])->name('hosting.websites.health');
    Route::post('hosting/websites/{website}/retry-provision', [AdminHostingController::class, 'retryProvision'])->name('hosting.websites.retry');
    Route::post('hosting/websites/{website}/mark-active', [AdminHostingController::class, 'markActive'])->name('hosting.websites.activate');
    Route::post('hosting/websites/{website}/service-url', [AdminHostingController::class, 'updateServiceUrl'])->name('hosting.websites.service-url');
    Route::post('hosting/websites/{website}/credentials', [AdminHostingController::class, 'issueCredential'])->name('hosting.websites.credentials');
    Route::delete('hosting/websites/{website}/credentials/{credential}', [AdminHostingController::class, 'revokeCredential'])->name('hosting.websites.credentials.revoke');
    Route::post('hosting/websites/{website}/domains/subdomain', [AdminHostingController::class, 'adminStoreSubdomain'])->name('hosting.domains.subdomain');
    Route::post('hosting/websites/{website}/domains', [AdminHostingController::class, 'adminStoreDomain'])->name('hosting.domains.custom');
    Route::post('hosting/websites/{website}/domains/{domain}/verify', [AdminHostingController::class, 'adminVerifyDomain'])->name('hosting.domains.verify');
    Route::post('hosting/websites/{website}/domains/{domain}/primary', [AdminHostingController::class, 'adminSetPrimary'])->name('hosting.domains.primary');
    Route::delete('hosting/websites/{website}/domains/{domain}', [AdminHostingController::class, 'adminDestroyDomain'])->name('hosting.domains.destroy');
    Route::post('hosting/websites/{website}/link-order', [AdminHostingController::class, 'linkOrder'])->name('hosting.websites.link-order');
    Route::get('hosting/clients', [AdminHostingController::class, 'clients'])->name('hosting.clients');
    Route::get('hosting/clients/{user}', [AdminHostingController::class, 'clientShow'])->name('hosting.clients.show');
    Route::get('hosting/domains', [AdminHostingController::class, 'domains'])->name('hosting.domains');
    Route::get('hosting/operations', [AdminHostingController::class, 'operations'])->name('hosting.operations');
    Route::post('hosting/operations/{command}/retry', [AdminHostingController::class, 'retryOperation'])->name('hosting.operations.retry');
    Route::get('hosting/health', [AdminHostingController::class, 'health'])->name('hosting.health');
    Route::get('hosting/audit', [AdminHostingController::class, 'audit'])->name('hosting.audit');
    Route::get('hosting/integrations', [AdminHostingController::class, 'integrations'])->name('hosting.integrations');
    Route::get('launch-signups', [AdminHostingController::class, 'launchSignups'])->name('launch.index');
    Route::patch('launch-signups/{signup}', [AdminHostingController::class, 'launchStatus'])->name('launch.status');
    Route::post('launch-signups/{signup}/create-user', [AdminHostingController::class, 'launchCreateUser'])->name('launch.create-user');
});

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('analytics', [DashboardController::class, 'analytics'])->name('analytics.index');
    Route::get('notifications/{id}/open', [DashboardController::class, 'openNotification'])->name('notifications.open');
    Route::post('notifications/read-all', [DashboardController::class, 'readAllNotifications'])->name('notifications.read-all');
    Route::get('notifications', [DashboardController::class, 'notifications'])->name('notifications.index');
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
    Route::get('hosting', [HostingController::class, 'index'])->name('hosting.index');
    Route::get('hosting/create', [HostingController::class, 'create'])->name('hosting.create');
    Route::post('hosting', [HostingController::class, 'store'])->name('hosting.store');
    Route::get('hosting/{website}', [HostingController::class, 'show'])->name('hosting.show');
    Route::delete('hosting/{website}', [HostingController::class, 'destroy'])->name('hosting.destroy');
    Route::get('hosting/{website}/domains', [HostingController::class, 'domains'])->name('hosting.domains');
    Route::post('hosting/{website}/domains', [HostingController::class, 'storeDomain'])->name('hosting.domains.store');
    Route::post('hosting/{website}/subdomains', [HostingController::class, 'storeSubdomain'])->name('hosting.subdomains.store');
    Route::patch('hosting/{website}/domains/{domain}', [HostingController::class, 'updateSubdomain'])->name('hosting.domains.update');
    Route::post('hosting/{website}/domains/{domain}/verify', [HostingController::class, 'verifyDomain'])->name('hosting.domains.verify');
    Route::post('hosting/{website}/domains/{domain}/primary', [HostingController::class, 'setPrimary'])->name('hosting.domains.primary');
    Route::delete('hosting/{website}/domains/{domain}', [HostingController::class, 'destroyDomain'])->name('hosting.domains.destroy');
    Route::get('hosting/{website}/analytics', [HostingController::class, 'analytics'])->name('hosting.analytics');
    Route::get('hosting/{website}/analytics/export', [HostingController::class, 'exportAnalytics'])->name('hosting.analytics.export');
    Route::get('hosting/{website}/health', [HostingController::class, 'health'])->name('hosting.health');
    Route::get('hosting/{website}/billing', [HostingController::class, 'billing'])->name('hosting.billing');
    Route::get('hosting/{website}/reports', [HostingController::class, 'reports'])->name('hosting.reports');
    Route::get('hosting/{website}/reports/pdf', [HostingController::class, 'reportPdf'])->name('hosting.reports.pdf');
    Route::post('hosting/{website}/suspend', [HostingController::class, 'requestSuspend'])->name('hosting.suspend');
    Route::post('hosting/{website}/reactivate', [HostingController::class, 'requestReactivate'])->name('hosting.reactivate');
});

// Guests may browse checkout and sign in inline; placing an order needs an account.
Route::get('dashboard/checkout', [CheckoutController::class, 'index'])->name('checkout');

require __DIR__.'/settings.php';
