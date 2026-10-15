<?php

use App\Models\BtContact;
use App\Models\BtDocument;
use App\Models\BtItem;
use App\Models\BtSetting;
use App\Models\User;

function btUser(): User
{
    return User::factory()->create();
}

function btCustomer(User $user): BtContact
{
    return BtContact::create([
        'user_id' => $user->id, 'kind' => BtContact::CUSTOMER,
        'name' => 'Test Customer', 'phone' => '+256 772 000001',
    ]);
}

function btItem(User $user, int $stock = 10): BtItem
{
    return BtItem::create([
        'user_id' => $user->id, 'name' => 'Cement 50kg', 'unit' => 'bags',
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

    expect(BtSetting::where('user_id', $user->id)->first()->business_name)->toBe('Savanna Build Ltd');
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

test('converting a quotation creates an invoice and deducts stock', function () {
    $user = btUser();
    $customer = btCustomer($user);
    $item = btItem($user, 10);

    $this->actingAs($user)->post('/dashboard/tools/quotations', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 3, 'unit_price' => '']],
    ])->assertRedirect();

    $quote = BtDocument::first();

    $this->actingAs($user)->post("/dashboard/tools/quotations/{$quote->id}/convert")->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toBe('INV-0001')
        ->and($invoice->total)->toBe(3 * 35000)
        ->and($invoice->status)->toBe('unpaid')
        ->and($quote->fresh()->status)->toBe('accepted')
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

    $this->actingAs($user)->get('/dashboard/tools/balance-sheet')->assertOk()
        ->assertSee('150,000', false);

    $this->actingAs($user)->get('/dashboard/tools/cashflow')->assertOk()
        ->assertSee('300,000', false);

    $this->actingAs($user)->get('/dashboard/tools/pnl')->assertOk()
        ->assertSee('Operating expenses', false);
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
        $this->actingAs($user)->get("/dashboard/tools/{$type}/create")->assertOk();
    }
});

test('tab carousel and create modals are present on every tab', function () {
    $user = btUser();

    foreach (['quotations', 'customers', 'inventory', 'expenses', 'pos'] as $tab) {
        $this->actingAs($user)->get("/dashboard/tools/{$tab}")->assertOk()
            ->assertSee('bt-tabs-prev', false)
            ->assertSee('bt-tabs-fade', false);
    }

    $this->actingAs($user)->get('/dashboard/tools/quotations')->assertOk()
        ->assertSee('id="doc-modal"', false);
    $this->actingAs($user)->get('/dashboard/tools/customers')->assertOk()
        ->assertSee('id="contact-modal"', false);
    $this->actingAs($user)->get('/dashboard/tools/inventory')->assertOk()
        ->assertSee('id="item-modal"', false);
    $this->actingAs($user)->get('/dashboard/tools/expenses')->assertOk()
        ->assertSee('id="expense-modal"', false);
});

test('custom dropdowns enhance every select on the tabs', function () {
    $user = btUser();

    foreach (['quotations', 'pos', 'expenses'] as $tab) {
        $this->actingAs($user)->get("/dashboard/tools/{$tab}")->assertOk()
            ->assertSee('enhanceSelect', false);
    }

    $this->actingAs($user)->get('/dashboard/tools/quotations/create')->assertOk()
        ->assertSee('enhanceSelect', false);
});
