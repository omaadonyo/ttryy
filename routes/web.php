<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminHostingController;
use App\Http\Controllers\BusinessToolsController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HostingController;
use App\Http\Controllers\IotecPaymentController;
use App\Http\Controllers\LaunchController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WalletController;
use App\Models\BtContact;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('launch', [LaunchController::class, 'index'])->name('launch');

// Public document authenticity check — the QR code on Business Tools PDFs lands here.
Route::get('tools/verify/{doc}', [BusinessToolsController::class, 'verify'])->name('tools.verify');

Route::post('launch', [LaunchController::class, 'store'])->name('launch.store');
Route::post('launch/iotec/pay', [IotecPaymentController::class, 'payLaunch'])->middleware('throttle:20,1')->name('launch.iotec.pay');
Route::get('launch/iotec/status', [IotecPaymentController::class, 'launchStatus'])->middleware('throttle:60,1')->name('launch.iotec.status');
Route::get('payments/iotec/card-return', [IotecPaymentController::class, 'cardReturn'])->name('payments.iotec.card-return');

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
    Route::patch('orders/{order}/payment', [AdminController::class, 'recordPayment'])->name('orders.payment');
    Route::patch('orders/{order}/handover', [AdminController::class, 'markHandedOver'])->name('orders.handover');
    Route::patch('orders/{order}/refund', [AdminController::class, 'refundOrder'])->name('orders.refund');
    Route::delete('orders/{order}', [AdminController::class, 'destroyOrder'])->name('orders.destroy');
    Route::get('payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('users', [AdminController::class, 'users'])->name('users');
    Route::patch('users/{user}/admin', [AdminController::class, 'toggleAdmin'])->name('users.admin');
    Route::patch('users/{user}/tokens', [AdminController::class, 'creditTokens'])->name('users.tokens');
    Route::patch('users/{user}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
    Route::patch('users/{user}/activate', [AdminController::class, 'activateUser'])->name('users.activate');
    Route::delete('users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('users/trashed/{id}/restore', [AdminController::class, 'restoreUser'])->name('users.restore');
    Route::delete('users/trashed/{id}/purge', [AdminController::class, 'purgeUser'])->name('users.purge');
    Route::get('packages', [AdminController::class, 'packages'])->name('packages');
    Route::get('hosting', [AdminHostingController::class, 'dashboard'])->name('hosting.dashboard');
    Route::get('hosting/websites', [AdminHostingController::class, 'websites'])->name('hosting.websites');
    Route::post('hosting/websites/trashed/{id}/recover', [AdminHostingController::class, 'recoverTrash'])->name('hosting.websites.recover');
    Route::delete('hosting/websites/trashed/{id}/purge', [AdminHostingController::class, 'purgeTrash'])->name('hosting.websites.purge');
    Route::get('hosting/websites/{website}', [AdminHostingController::class, 'websiteShow'])->name('hosting.websites.show');
    Route::delete('hosting/websites/{website}', [AdminHostingController::class, 'destroyWebsite'])->name('hosting.websites.destroy');
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
    Route::get('search', SearchController::class)->name('dashboard.search');
    Route::redirect('orders/new', '/checkout');
    Route::get('payments', [DashboardController::class, 'payments'])->name('payments.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('checkout/verify', [CheckoutController::class, 'verify'])->name('checkout.verify');
    Route::post('checkout/iotec/pay', [IotecPaymentController::class, 'payOrder'])->middleware('throttle:30,1')->name('checkout.iotec.pay');
    Route::post('checkout/iotec/card', [IotecPaymentController::class, 'payOrderCard'])->middleware('throttle:30,1')->name('checkout.iotec.card');
    Route::get('checkout/iotec/status', [IotecPaymentController::class, 'orderStatus'])->name('checkout.iotec.status');
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
    Route::post('scraper/search', [MarketingController::class, 'scraperSearch'])->middleware('throttle:20,1')->name('scraper.search');
    Route::post('groups/{group}/unlock', [MarketingController::class, 'unlockGroup'])->name('groups.unlock');
    Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');
    Route::post('wallet/verify', [WalletController::class, 'verify'])->name('wallet.verify');
    Route::post('wallet/topup-cash', [WalletController::class, 'topupCash'])->name('wallet.topup-cash');
    Route::post('wallet/verify-cash', [WalletController::class, 'verifyCash'])->name('wallet.verify-cash');
    Route::post('orders/{order}/pay', [OrderPaymentController::class, 'store'])->name('orders.pay');
    Route::get('orders/{order}/pay/status', [OrderPaymentController::class, 'status'])->name('orders.pay.status');
    Route::get('hosting', [HostingController::class, 'index'])->name('hosting.index');
    Route::get('hosting/create', [HostingController::class, 'create'])->name('hosting.create');
    Route::post('hosting', [HostingController::class, 'store'])->name('hosting.store');
    Route::get('hosting/{website}', [HostingController::class, 'show'])->name('hosting.show');
    Route::delete('hosting/{website}', [HostingController::class, 'destroy'])->name('hosting.destroy');
    Route::post('hosting/trashed/{id}/restore', [HostingController::class, 'restoreTrash'])->name('hosting.restore');
    Route::get('hosting/{website}/domains', [HostingController::class, 'domains'])->name('hosting.domains');
    Route::get('hosting/{website}/analytics', [HostingController::class, 'analytics'])->name('hosting.analytics');
    Route::get('hosting/{website}/analytics/export', [HostingController::class, 'exportAnalytics'])->name('hosting.analytics.export');
    Route::get('hosting/{website}/health', [HostingController::class, 'health'])->name('hosting.health');
    Route::get('hosting/{website}/billing', [HostingController::class, 'billing'])->name('hosting.billing');
    Route::get('hosting/{website}/reports', [HostingController::class, 'reports'])->name('hosting.reports');
    Route::get('hosting/{website}/reports/pdf', [HostingController::class, 'reportPdf'])->name('hosting.reports.pdf');
    Route::post('hosting/{website}/suspend', [HostingController::class, 'requestSuspend'])->name('hosting.suspend');
    Route::post('hosting/{website}/reactivate', [HostingController::class, 'requestReactivate'])->name('hosting.reactivate');
    Route::prefix('tools')->name('tools.')->group(function () {
        Route::get('/', [BusinessToolsController::class, 'index'])->name('index');
        Route::get('api/state', [BusinessToolsController::class, 'apiState'])->name('api.state');
        Route::get('api/reports', [BusinessToolsController::class, 'apiReports'])->name('api.reports');
        Route::get('api/fx', [BusinessToolsController::class, 'apiFx'])->name('api.fx');
        Route::get('api/activity', [BusinessToolsController::class, 'apiActivity'])->name('api.activity');
        Route::get('api/notifications', [BusinessToolsController::class, 'apiNotifications'])->name('api.notifications');
        Route::get('api/documents/{doc}', [BusinessToolsController::class, 'apiDoc'])->name('api.doc');
        Route::post('notifications/read', [BusinessToolsController::class, 'readNotifications'])->name('notifications.read');
        Route::get('reports', [BusinessToolsController::class, 'index'])->name('reports');
        Route::get('settings', [BusinessToolsController::class, 'settingsPage'])->name('settings');
        Route::patch('settings', [BusinessToolsController::class, 'updateSettings'])->name('settings.update');
        Route::post('businesses', [BusinessToolsController::class, 'storeBusiness'])->name('businesses.store');
        Route::patch('businesses/{business}', [BusinessToolsController::class, 'updateBusiness'])->name('businesses.update');
        Route::delete('businesses/{business}', [BusinessToolsController::class, 'destroyBusiness'])->name('businesses.destroy');
        Route::post('businesses/{business}/switch', [BusinessToolsController::class, 'switchBusiness'])->name('businesses.switch');
        Route::get('staff', [BusinessToolsController::class, 'staff'])->name('staff');
        Route::post('staff', [BusinessToolsController::class, 'storeStaff'])->name('staff.store');
        Route::patch('staff/{staff}', [BusinessToolsController::class, 'updateStaff'])->name('staff.update');
        Route::delete('staff/{staff}', [BusinessToolsController::class, 'destroyStaff'])->name('staff.destroy');
        Route::get('activity', [BusinessToolsController::class, 'activity'])->name('activity');
        Route::get('notifications', [BusinessToolsController::class, 'notifications'])->name('notifications');
        Route::get('api/items/{item}', [BusinessToolsController::class, 'apiItem'])->name('api.item');
        Route::get('reminders', [BusinessToolsController::class, 'reminders'])->name('reminders');
        Route::post('reminders', [BusinessToolsController::class, 'storeReminder'])->name('reminders.store');
        Route::get('cashflow', [BusinessToolsController::class, 'cashflow'])->name('cashflow');
        Route::get('pnl', [BusinessToolsController::class, 'pnl'])->name('pnl');
        Route::get('balance-sheet', [BusinessToolsController::class, 'balancesheet'])->name('balancesheet');
        Route::get('customers', fn () => app(BusinessToolsController::class)->contacts(request(), BtContact::CUSTOMER))->name('customers');
        Route::get('suppliers', fn () => app(BusinessToolsController::class)->contacts(request(), BtContact::SUPPLIER))->name('suppliers');
        Route::post('contacts', [BusinessToolsController::class, 'storeContact'])->name('contacts.store');
        Route::patch('contacts/{contact}', [BusinessToolsController::class, 'updateContact'])->name('contacts.update');
        Route::delete('contacts/{contact}', [BusinessToolsController::class, 'destroyContact'])->name('contacts.destroy');
        Route::get('inventory', [BusinessToolsController::class, 'inventory'])->name('inventory');
        Route::post('items', [BusinessToolsController::class, 'storeItem'])->name('items.store');
        Route::patch('items/{item}', [BusinessToolsController::class, 'updateItem'])->name('items.update');
        Route::delete('items/{item}', [BusinessToolsController::class, 'destroyItem'])->name('items.destroy');
        Route::post('items/{item}/adjust', [BusinessToolsController::class, 'adjustStock'])->name('items.adjust');
        Route::get('quotations', [BusinessToolsController::class, 'quotations'])->name('quotations');
        Route::get('sales-orders', [BusinessToolsController::class, 'salesOrders'])->name('salesorders');
        Route::get('delivery-notes', [BusinessToolsController::class, 'deliveryNotes'])->name('deliverynotes');
        Route::get('invoices', [BusinessToolsController::class, 'invoices'])->name('invoices');
        Route::get('receipts', [BusinessToolsController::class, 'receipts'])->name('receipts');
        Route::get('credit-notes', [BusinessToolsController::class, 'creditNotes'])->name('creditnotes');
        Route::get('purchase-quotes', [BusinessToolsController::class, 'purchaseQuotes'])->name('purchasequotes');
        Route::get('purchase-orders', [BusinessToolsController::class, 'purchaseOrders'])->name('purchaseorders');
        Route::get('purchase-invoices', [BusinessToolsController::class, 'purchaseInvoices'])->name('purchaseinvoices');
        Route::get('purchase-payments', [BusinessToolsController::class, 'purchasePayments'])->name('purchasepayments');
        Route::get('accounts', [BusinessToolsController::class, 'accounts'])->name('accounts');
        Route::get('bank', [BusinessToolsController::class, 'bank'])->name('bank');
        Route::get('employees', [BusinessToolsController::class, 'employees'])->name('employees');
        Route::get('payslips', [BusinessToolsController::class, 'payslips'])->name('payslips');
        Route::get('journal', [BusinessToolsController::class, 'journal'])->name('journal');
        $docTypes = ['quotations', 'sales_orders', 'delivery_notes', 'invoices', 'receipts', 'credit_notes', 'purchase_quotes', 'purchase_orders', 'purchase_invoices', 'purchase_payments'];
        Route::get('{type}/create', [BusinessToolsController::class, 'createDoc'])->whereIn('type', $docTypes)->name('doc.create');
        Route::post('{type}', [BusinessToolsController::class, 'storeDoc'])->whereIn('type', $docTypes)->name('doc.store');
        Route::get('documents/{doc}', [BusinessToolsController::class, 'showDoc'])->name('doc.show');
        Route::get('documents/{doc}/pdf', [BusinessToolsController::class, 'docPdf'])->name('doc.pdf');
        Route::delete('documents/{doc}', [BusinessToolsController::class, 'destroyDoc'])->name('doc.destroy');
        Route::patch('documents/{doc}/status', [BusinessToolsController::class, 'updateDocStatus'])->name('doc.status');
        Route::post('documents/{doc}/convert', [BusinessToolsController::class, 'convertDoc'])->name('doc.convert');
        Route::post('documents/{doc}/clone', [BusinessToolsController::class, 'cloneDoc'])->name('doc.clone');
        Route::post('accounts', [BusinessToolsController::class, 'storeAccount'])->name('accounts.store');
        Route::patch('accounts/{account}', [BusinessToolsController::class, 'updateAccount'])->name('accounts.update');
        Route::delete('accounts/{account}', [BusinessToolsController::class, 'destroyAccount'])->name('accounts.destroy');
        Route::post('bank', [BusinessToolsController::class, 'storeBank'])->name('bank.store');
        Route::patch('bank/{account}', [BusinessToolsController::class, 'updateBank'])->name('bank.update');
        Route::delete('bank/{account}', [BusinessToolsController::class, 'destroyBank'])->name('bank.destroy');
        Route::post('bank/{account}/transactions', [BusinessToolsController::class, 'storeBankTransaction'])->name('bank.tx.store');
        Route::delete('bank-transactions/{transaction}', [BusinessToolsController::class, 'destroyBankTransaction'])->name('bank.tx.destroy');
        Route::post('employees', [BusinessToolsController::class, 'storeEmployee'])->name('employees.store');
        Route::patch('employees/{employee}', [BusinessToolsController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('employees/{employee}', [BusinessToolsController::class, 'destroyEmployee'])->name('employees.destroy');
        Route::post('payslips', [BusinessToolsController::class, 'storePayslip'])->name('payslips.store');
        Route::patch('payslips/{payslip}', [BusinessToolsController::class, 'updatePayslip'])->name('payslips.update');
        Route::delete('payslips/{payslip}', [BusinessToolsController::class, 'destroyPayslip'])->name('payslips.destroy');
        Route::get('pos', [BusinessToolsController::class, 'pos'])->name('pos');
        Route::post('pos/checkout', [BusinessToolsController::class, 'posCheckout'])->name('pos.checkout');
        Route::get('expenses', [BusinessToolsController::class, 'expenses'])->name('expenses');
        Route::post('expenses', [BusinessToolsController::class, 'storeExpense'])->name('expenses.store');
        Route::patch('expenses/{expense}', [BusinessToolsController::class, 'updateExpense'])->name('expenses.update');
        Route::delete('expenses/{expense}', [BusinessToolsController::class, 'destroyExpense'])->name('expenses.destroy');
    });
});

// Guests may browse checkout and sign in inline; placing an order needs an account.
Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::get('dashboard/checkout', fn () => redirect('/checkout'));

require __DIR__.'/settings.php';
