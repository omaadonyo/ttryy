<?php

use App\Mail\InvoiceReminderMail;
use App\Models\ActivityLog;
use App\Models\BtContact;
use App\Models\BtDocument;
use App\Models\BtItem;
use App\Models\Business;
use App\Models\Reminder;
use App\Models\User;
use App\Services\BusinessContext;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

function btUser(bool $premium = true): User
{
    $user = User::factory()->create();
    $business = app(BusinessContext::class)->forUser($user);

    // Most tests exercise paid features; free-plan limits have their own tests.
    if ($premium) {
        $business->forceFill(['plan' => 'premium', 'premium_until' => now()->addMonth()])->save();
    }

    return $user;
}

function btCustomer(User $user): BtContact
{
    return BtContact::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'kind' => BtContact::CUSTOMER,
        'name' => 'Test Customer', 'phone' => '+256 772 000001',
        'email' => 'customer@example.com',
    ]);
}

function btItem(User $user, int $stock = 10): BtItem
{
    return BtItem::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'name' => 'Cement 50kg', 'unit' => 'bags',
        'cost_price' => 28000, 'sale_price' => 35000, 'stock' => $stock,
    ]);
}

test('guests cannot access business tools', function () {
    $this->get('/dashboard/tools')->assertRedirect('/login');
});

test('reports dashboard loads with all twelve tabs', function () {
    $this->actingAs(btUser())->get('/dashboard/tools')->assertOk()
        ->assertSee('Business Tools', false)
        ->assertSee('Quotations', false)
        ->assertSee('Invoicing', false)
        ->assertSee('Receipts', false)
        ->assertSee('POS', false)
        ->assertSee('Inventory', false)
        ->assertSee('Customers', false)
        ->assertSee('Suppliers', false)
        ->assertSee('Expenses', false)
        ->assertSee('Cash Flow', false)
        ->assertSee('P&amp;L', false)
        ->assertSee('Balance Sheet', false);
});

test('business profile saves and prints intent', function () {
    $user = btUser();

    $this->actingAs($user)->patch('/dashboard/tools/settings', [
        'business_name' => 'Savanna Build Ltd',
        'phone' => '+256 700 000000',
        'tax_rate' => 18,
    ])->assertRedirect();

    expect(Business::where('owner_id', $user->id)->first()->name)->toBe('Savanna Build Ltd');
});

test('customers are isolated per user', function () {
    $a = btUser();
    $b = btUser();
    $contact = btCustomer($a);

    $this->actingAs($b)->patch("/dashboard/tools/contacts/{$contact->id}", ['name' => 'Hijack'])->assertForbidden();
    $this->actingAs($a)->patch("/dashboard/tools/contacts/{$contact->id}", ['name' => 'Renamed'])->assertRedirect();
    expect($contact->fresh()->name)->toBe('Renamed');
});

test('inventory tracks stock and reorder levels', function () {
    $user = btUser();

    $this->actingAs($user)->post('/dashboard/tools/items', [
        'name' => 'Cement 50kg', 'unit' => 'bags',
        'cost_price' => 28000, 'sale_price' => 35000, 'stock' => 10, 'reorder_level' => 5,
    ])->assertRedirect();

    $item = BtItem::first();
    expect($item->stock)->toBe(10)->and($item->isLow())->toBeFalse();

    $this->actingAs($user)->post("/dashboard/tools/items/{$item->id}/adjust", [
        'qty_change' => -7, 'reason' => 'Damaged bags',
    ])->assertRedirect();

    expect($item->fresh()->stock)->toBe(3)->and($item->fresh()->isLow())->toBeTrue();
});

test('quotations compute totals server-side and number sequentially', function () {
    $user = btUser();
    $customer = btCustomer($user);
    $item = btItem($user);

    $this->actingAs($user)->post('/dashboard/tools/quotations', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'discount' => 5000,
        'tax_rate' => 18,
        'lines' => [
            ['item_id' => $item->id, 'description' => '', 'qty' => 2, 'unit_price' => ''],
            ['description' => 'Delivery to site', 'qty' => 1, 'unit_price' => 20000],
        ],
    ])->assertRedirect();

    $quote = BtDocument::first();
    // subtotal = 2*35000 + 20000 = 90000; discount 5000 → 85000; tax 18% = 15300; total 100300
    expect($quote->number)->toBe('QT-0001')
        ->and($quote->subtotal)->toBe(90000)
        ->and($quote->total)->toBe(100300)
        ->and($quote->status)->toBe('draft')
        ->and($item->fresh()->stock)->toBe(10);

    $this->actingAs($user)->post('/dashboard/tools/quotations', [
        'issue_date' => now()->toDateString(),
        'lines' => [['description' => 'Service', 'qty' => 1, 'unit_price' => 1000]],
    ])->assertRedirect();
    expect(BtDocument::orderByDesc('id')->first()->number)->toBe('QT-0002');
});

test('converting a quotation flows through a sales order to a stock-deducting invoice', function () {
    $user = btUser();
    $customer = btCustomer($user);
    $item = btItem($user, 10);

    $this->actingAs($user)->post('/dashboard/tools/quotations', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 3, 'unit_price' => '']],
    ])->assertRedirect();

    $quote = BtDocument::first();

    $this->actingAs($user)->post("/dashboard/tools/documents/{$quote->id}/convert")->assertRedirect();

    $order = BtDocument::where('type', 'sales_order')->first();
    expect($order)->not->toBeNull()
        ->and($order->number)->toBe('SO-0001')
        ->and($quote->fresh()->status)->toBe('accepted');

    $this->actingAs($user)->post("/dashboard/tools/documents/{$order->id}/convert")->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toBe('INV-0001')
        ->and($invoice->total)->toBe(3 * 35000)
        ->and($invoice->status)->toBe('unpaid')
        ->and($invoice->converted_from_id)->toBe($order->id)
        ->and($order->fresh()->status)->toBe('fulfilled')
        ->and($item->fresh()->stock)->toBe(7);
});

test('invoices refuse to oversell stock', function () {
    $user = btUser();
    $item = btItem($user, 2);

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 5, 'unit_price' => '']],
    ])->assertSessionHasErrors('lines');

    expect(BtDocument::count())->toBe(0)->and($item->fresh()->stock)->toBe(2);
});

test('recording payments moves an invoice from unpaid to paid', function () {
    $user = btUser();
    $customer = btCustomer($user);
    btItem($user, 50);

    $item = BtItem::first();

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 2, 'unit_price' => '']],
    ])->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();
    expect($invoice->total)->toBe(70000);

    // partial payment
    $this->actingAs($user)->post('/dashboard/tools/receipts', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'invoice_id' => $invoice->id,
        'payment_method' => 'momo',
        'amount' => 20000,
    ])->assertRedirect();

    expect($invoice->fresh()->status)->toBe('partial')
        ->and($invoice->fresh()->paid_amount)->toBe(20000);

    // overpaying is rejected
    $this->actingAs($user)->post('/dashboard/tools/receipts', [
        'issue_date' => now()->toDateString(),
        'invoice_id' => $invoice->id,
        'amount' => 60000,
    ])->assertSessionHasErrors('amount');

    // final payment
    $this->actingAs($user)->post('/dashboard/tools/receipts', [
        'issue_date' => now()->toDateString(),
        'invoice_id' => $invoice->id,
        'amount' => 50000,
    ])->assertRedirect();

    expect($invoice->fresh()->status)->toBe('paid')
        ->and(BtDocument::where('type', 'receipt')->count())->toBe(2);
});

test('paid invoices cannot be deleted but unpaid ones restore stock', function () {
    $user = btUser();
    btCustomer($user);
    $item = btItem($user, 10);

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 4, 'unit_price' => '']],
    ])->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();
    expect($item->fresh()->stock)->toBe(6);

    $this->actingAs($user)->delete("/dashboard/tools/documents/{$invoice->id}")->assertRedirect();
    expect(BtDocument::count())->toBe(0)->and($item->fresh()->stock)->toBe(10);

    // paid invoice: locked
    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 1, 'unit_price' => '']],
    ])->assertRedirect();

    $paid = BtDocument::where('type', 'invoice')->first();
    $this->actingAs($user)->post('/dashboard/tools/receipts', [
        'issue_date' => now()->toDateString(),
        'invoice_id' => $paid->id,
        'amount' => $paid->total,
    ])->assertRedirect();

    $this->actingAs($user)->delete("/dashboard/tools/documents/{$paid->id}")->assertRedirect();
    expect($paid->fresh()->exists)->toBeTrue();
});

test('pos checkout sells stock and issues invoice plus receipt', function () {
    $user = btUser();
    $item = btItem($user, 10);

    $this->actingAs($user)->get('/dashboard/tools/pos')->assertOk()
        ->assertSee('Current sale', false);

    $this->actingAs($user)->post('/dashboard/tools/pos/checkout', [
        'payment_method' => 'cash',
        'tendered' => 100000,
        'lines' => [['item_id' => $item->id, 'qty' => 2]],
    ])->assertRedirect();

    expect($item->fresh()->stock)->toBe(8)
        ->and(BtDocument::where('type', 'invoice')->first()->status)->toBe('paid')
        ->and(BtDocument::where('type', 'receipt')->count())->toBe(1);
});

test('expenses feed the balance sheet as payables when unpaid', function () {
    $user = btUser();

    $this->actingAs($user)->post('/dashboard/tools/expenses', [
        'date' => now()->toDateString(), 'category' => 'Rent', 'amount' => 300000, 'status' => 'paid',
    ])->assertRedirect();

    $this->actingAs($user)->post('/dashboard/tools/expenses', [
        'date' => now()->toDateString(), 'category' => 'Stock purchase', 'amount' => 150000, 'status' => 'unpaid',
    ])->assertRedirect();

    $this->actingAs($user)->getJson('/dashboard/tools/api/reports?from='.now()->startOfMonth()->toDateString().'&to='.now()->toDateString())
        ->assertOk()
        ->assertJsonPath('balance.payables', 150000)
        ->assertJsonPath('pnl.expense_total', 300000);

    $this->actingAs($user)->get('/dashboard/tools/balance-sheet')->assertOk()
        ->assertSee('data-bt-app', false);
});

test('document pdfs download and verify pages prove authenticity', function () {
    $user = btUser();
    $customer = btCustomer($user);
    btItem($user, 10);
    $item = BtItem::first();

    $this->actingAs($user)->post('/dashboard/tools/quotations', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 1, 'unit_price' => '']],
    ])->assertRedirect();

    $quote = BtDocument::first();

    $pdf = $this->actingAs($user)->get("/dashboard/tools/documents/{$quote->id}/pdf");
    $pdf->assertOk();
    expect($pdf->headers->get('Content-Type'))->toContain('application/pdf');

    $this->get("/tools/verify/{$quote->id}?token={$quote->public_token}")
        ->assertOk()
        ->assertSee($quote->number, false)
        ->assertSee('VERIFIED DOCUMENT', false);

    $this->get("/tools/verify/{$quote->id}?token=wrong-token")->assertNotFound();
});

test('users cannot open each others documents', function () {
    $a = btUser();
    btCustomer($a);
    btItem($a, 5);

    $this->actingAs($a)->post('/dashboard/tools/invoices', [
        'issue_date' => now()->toDateString(),
        'lines' => [['description' => 'Service', 'qty' => 1, 'unit_price' => 5000]],
    ])->assertRedirect();

    $doc = BtDocument::first();

    $this->actingAs(btUser())->get("/dashboard/tools/documents/{$doc->id}")->assertForbidden();
    $this->actingAs(btUser())->get("/dashboard/tools/documents/{$doc->id}/pdf")->assertForbidden();
});

test('all three create pages render without errors', function () {
    $user = btUser();

    foreach (['quotations', 'invoices', 'receipts'] as $type) {
        $this->actingAs($user)->get("/dashboard/tools/{$type}/create")->assertOk()
            ->assertSee('data-bt-app', false);
    }
});

test('the standalone spa shell boots on every tab', function () {
    $user = btUser();

    $paths = ['', 'quotations', 'sales-orders', 'delivery-notes', 'invoices', 'receipts', 'credit-notes', 'purchase-quotes', 'purchase-orders', 'purchase-invoices', 'purchase-payments', 'accounts', 'bank', 'employees', 'payslips', 'journal', 'pos', 'inventory', 'customers', 'suppliers', 'expenses', 'reminders', 'staff', 'activity', 'notifications', 'cashflow', 'pnl', 'balance-sheet', 'reports', 'settings'];

    foreach ($paths as $path) {
        $this->actingAs($user)->get('/dashboard/tools'.($path ? '/'.$path : ''))->assertOk()
            ->assertSee('data-bt-app', false)
            ->assertSee('window.btTools', false)
            ->assertSee('Dashboard', false);
    }
});

test('the spa state endpoint only exposes the current user records', function () {
    $a = btUser();
    $b = btUser();
    btCustomer($a);

    $this->actingAs($b)->getJson('/dashboard/tools/api/state')->assertOk()
        ->assertJsonCount(0, 'contacts');

    $this->actingAs($a)->getJson('/dashboard/tools/api/state')->assertOk()
        ->assertJsonCount(1, 'contacts');
});

test('document creation returns json for the spa', function () {
    $user = btUser();
    $item = btItem($user, 5);

    $this->actingAs($user)->postJson('/dashboard/tools/invoices', [
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'qty' => 1, 'unit_price' => 35000]],
    ])->assertOk()
        ->assertJsonPath('document.number', 'INV-0001')
        ->assertJsonPath('document.total', 35000)
        ->assertJsonPath('document.status', 'unpaid');
});

test('a business owner can create and switch between businesses', function () {
    $user = btUser();
    $first = $user->active_business_id;

    $this->actingAs($user)->postJson('/dashboard/tools/businesses', [
        'name' => 'Second Ventures',
    ])->assertOk()->assertJsonPath('state.business.name', 'Second Ventures');

    $second = Business::where('name', 'Second Ventures')->first();
    expect($user->fresh()->active_business_id)->toBe($second->id)
        ->and($user->businesses()->count())->toBe(2);

    $this->actingAs($user)->postJson("/dashboard/tools/businesses/{$first}/switch")
        ->assertOk()->assertJsonPath('state.business.id', $first);

    expect($user->fresh()->active_business_id)->toBe($first);
});

test('business data is isolated between the two businesses of one owner', function () {
    $user = btUser();
    btItem($user, 5);

    $this->actingAs($user)->postJson('/dashboard/tools/businesses', ['name' => 'Clean Slate'])
        ->assertOk();

    // New business sees none of the first business's items.
    $this->actingAs($user)->getJson('/dashboard/tools/api/state')
        ->assertOk()->assertJsonCount(0, 'items');
});

test('staff invited by email share the owners business data', function () {
    Notification::fake();
    $owner = btUser();
    $item = btItem($owner, 5);

    $this->actingAs($owner)->postJson('/dashboard/tools/staff', [
        'email' => 'teammate@example.com', 'name' => 'Team Mate', 'role' => 'staff',
    ])->assertOk()->assertJsonFragment(['email' => 'teammate@example.com']);

    $staff = User::where('email', 'teammate@example.com')->first();
    expect($staff)->not->toBeNull()
        ->and($staff->businesses()->whereKey($owner->active_business_id)->exists())->toBeTrue()
        ->and($staff->fresh()->active_business_id)->toBeNull();

    // Staff can see the owner's stock.
    $this->actingAs($staff)->getJson('/dashboard/tools/api/state')
        ->assertOk()->assertJsonCount(1, 'items');

    // But a staff member cannot invite others or edit settings.
    $this->actingAs($staff)->postJson('/dashboard/tools/staff', ['email' => 'x@example.com'])
        ->assertForbidden();
    $this->actingAs($staff)->patchJson('/dashboard/tools/settings', ['business_name' => 'Hijack'])
        ->assertForbidden();
});

test('email reminders are sent and whatsapp reminders return a click to chat link', function () {
    Mail::fake();
    $user = btUser();
    $customer = btCustomer($user);
    $item = btItem($user, 5);

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'qty' => 1, 'unit_price' => '']],
    ])->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();

    $this->actingAs($user)->postJson('/dashboard/tools/reminders', [
        'document_id' => $invoice->id, 'channel' => 'email',
    ])->assertOk();

    Mail::assertSent(InvoiceReminderMail::class);
    expect(Reminder::where('channel', 'email')->first()->sent_at)->not->toBeNull();

    $this->actingAs($user)->postJson('/dashboard/tools/reminders', [
        'document_id' => $invoice->id, 'channel' => 'whatsapp',
    ])->assertOk()->assertJsonPath('wa_url', fn ($url) => str_starts_with($url, 'https://wa.me/'));

    expect(Reminder::where('channel', 'whatsapp')->first()->sent_at)->toBeNull();
});

test('business actions are written to the activity log with the actor', function () {
    $user = btUser();

    $this->actingAs($user)->post('/dashboard/tools/expenses', [
        'date' => now()->toDateString(), 'category' => 'Rent', 'amount' => 1000, 'status' => 'paid',
    ])->assertRedirect();

    $log = ActivityLog::where('action', 'expense.created')->first();
    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id)
        ->and($log->business_id)->toBe($user->active_business_id);

    $this->actingAs($user)->getJson('/dashboard/tools/api/activity')
        ->assertOk()->assertJsonPath('activity.0.action', 'expense.created');
});

test('signing in records the ip address on the user', function () {
    $user = User::factory()->create(['password' => bcrypt('secret-password')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'secret-password']);

    expect($user->fresh()->last_login_at)->not->toBeNull()
        ->and($user->fresh()->last_login_ip)->toBe('127.0.0.1');
});

test('the item history endpoint returns the stock moves for the current business', function () {
    $user = btUser();
    $item = btItem($user, 10);

    $this->actingAs($user)->post("/dashboard/tools/items/{$item->id}/adjust", [
        'qty_change' => 5, 'reason' => 'Restock',
    ])->assertRedirect();

    $this->actingAs($user)->getJson("/dashboard/tools/api/items/{$item->id}")
        ->assertOk()
        ->assertJsonPath('item.id', $item->id)
        ->assertJsonCount(1, 'moves')
        ->assertJsonPath('moves.0.qty_change', 5);

    $other = btUser();
    $this->actingAs($other)->getJson("/dashboard/tools/api/items/{$item->id}")->assertForbidden();
});

test('an invoice exposes the date of its most recent receipt in the spa state', function () {
    $user = btUser();

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'issue_date' => now()->toDateString(), 'due_date' => now()->addWeek()->toDateString(),
        'lines' => [['description' => 'Service', 'qty' => 1, 'unit_price' => 500]],
    ]);

    $invoice = BtDocument::where('type', 'invoice')->latest('id')->first();

    $this->actingAs($user)->post('/dashboard/tools/receipts', [
        'invoice_id' => $invoice->id, 'amount' => 200, 'payment_method' => 'cash',
        'issue_date' => now()->toDateString(),
    ]);

    $receipt = BtDocument::where('type', 'receipt')->latest('id')->first();

    $this->actingAs($user)->getJson('/dashboard/tools/api/documents/'.$invoice->id)
        ->assertOk()
        ->assertJsonPath('document.last_receipt_date', $receipt->issue_date->toDateString());
});
