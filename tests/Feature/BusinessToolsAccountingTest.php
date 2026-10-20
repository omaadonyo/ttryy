<?php

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\BtContact;
use App\Models\BtDocument;
use App\Models\BtItem;
use App\Models\Business;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\Payslip;
use App\Models\User;
use App\Services\BusinessContext;

function accUser(bool $premium = true): User
{
    $user = User::factory()->create();
    $business = app(BusinessContext::class)->forUser($user);

    if ($premium) {
        $business->forceFill(['plan' => 'premium', 'premium_until' => now()->addMonth()])->save();
    }

    return $user;
}

function accSupplier(User $user): BtContact
{
    return BtContact::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'kind' => BtContact::SUPPLIER,
        'name' => 'Test Supplier', 'phone' => '+256 772 000002',
    ]);
}

function accItem(User $user, int $stock = 0): BtItem
{
    return BtItem::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'name' => 'Steel bar', 'unit' => 'pcs',
        'cost_price' => 5000, 'sale_price' => 8000, 'stock' => $stock,
    ]);
}

test('business profile stores per-document footers and defaults to usd', function () {
    $user = accUser();

    $this->actingAs($user)->patch('/dashboard/tools/settings', [
        'business_name' => 'Acme Ltd',
        'receipt_footer' => 'Thanks!',
        'quotation_footer' => 'Valid 30 days',
        'invoice_footer' => 'Pay within 15 days',
    ])->assertRedirect();

    $business = Business::find($user->active_business_id);
    expect($business->currency)->toBe('USD')
        ->and($business->quotation_footer)->toBe('Valid 30 days')
        ->and($business->invoice_footer)->toBe('Pay within 15 days');
});

test('the chart of accounts is seeded and custom accounts can be managed', function () {
    $user = accUser();

    expect(Account::where('business_id', $user->active_business_id)->count())->toBeGreaterThan(5);

    $this->actingAs($user)->post('/dashboard/tools/accounts', [
        'code' => '6999', 'name' => 'Special projects', 'type' => 'expense', 'opening_balance' => 0,
    ])->assertRedirect();

    $account = Account::where('code', '6999')->first();
    expect($account)->not->toBeNull()->and($account->is_system)->toBeFalse();

    $this->actingAs($user)->patch("/dashboard/tools/accounts/{$account->id}", [
        'name' => 'Special projects 2026',
    ])->assertRedirect();
    expect($account->fresh()->name)->toBe('Special projects 2026');

    $this->actingAs($user)->delete("/dashboard/tools/accounts/{$account->id}")->assertRedirect();
    expect(Account::find($account->id))->toBeNull();

    $system = Account::where('business_id', $user->active_business_id)->where('is_system', true)->first();
    $this->actingAs($user)->delete("/dashboard/tools/accounts/{$system->id}")->assertSessionHasErrors('account');
});

test('bank accounts track deposits and withdrawals in their running balance', function () {
    $user = accUser();

    $this->actingAs($user)->post('/dashboard/tools/bank', [
        'name' => 'Main account', 'type' => 'bank', 'account_number' => '0123456789', 'opening_balance' => 100000,
    ])->assertRedirect();

    $bank = BankAccount::first();
    expect($bank)->not->toBeNull()->and($bank->balance())->toBe(100000);

    $this->actingAs($user)->post("/dashboard/tools/bank/{$bank->id}/transactions", [
        'date' => now()->toDateString(), 'description' => 'Cash sale', 'amount' => 50000, 'type' => 'credit',
    ])->assertRedirect();

    $this->actingAs($user)->post("/dashboard/tools/bank/{$bank->id}/transactions", [
        'date' => now()->toDateString(), 'description' => 'Fuel', 'amount' => 30000, 'type' => 'debit',
    ])->assertRedirect();

    expect($bank->fresh()->balance())->toBe(120000)
        ->and(BankTransaction::where('bank_account_id', $bank->id)->count())->toBe(2)
        ->and(BankTransaction::where('type', BankTransaction::DEBIT)->first()->amount)->toBe(-30000);

    $tx = BankTransaction::first();
    $this->actingAs($user)->delete("/dashboard/tools/bank-transactions/{$tx->id}")->assertRedirect();
    expect(BankTransaction::count())->toBe(1);
});

test('employees and payslips post salaries to the ledger', function () {
    $user = accUser();

    $this->actingAs($user)->post('/dashboard/tools/employees', [
        'name' => 'Jane Doe', 'position' => 'Accountant', 'employment_type' => 'full_time',
        'base_salary' => 1500000, 'pay_frequency' => 'monthly', 'is_active' => true,
    ])->assertRedirect();

    $employee = Employee::first();
    expect($employee)->not->toBeNull()->and($employee->name)->toBe('Jane Doe');

    $this->actingAs($user)->post('/dashboard/tools/payslips', [
        'employee_id' => $employee->id,
        'period_start' => now()->startOfMonth()->toDateString(),
        'period_end' => now()->toDateString(),
        'gross' => 1500000, 'allowances' => 100000, 'deductions' => 50000,
        'status' => 'paid', 'paid_on' => now()->toDateString(),
    ])->assertRedirect();

    $payslip = Payslip::first();
    expect($payslip)->not->toBeNull()
        ->and($payslip->number)->toBe('PS-0001')
        ->and($payslip->net)->toBe(1550000)
        ->and(JournalEntry::where('source_type', 'bt_payslip')->count())->toBe(1);

    $this->actingAs($user)->delete("/dashboard/tools/payslips/{$payslip->id}")->assertRedirect();
    expect(Payslip::count())->toBe(0)
        ->and(JournalEntry::where('source_type', 'bt_payslip')->count())->toBe(0);
});

test('a purchase quote converts to an order then a stock-receiving bill', function () {
    $user = accUser();
    $supplier = accSupplier($user);
    $item = accItem($user, 0);

    $this->actingAs($user)->post('/dashboard/tools/purchase_quotes', [
        'contact_id' => $supplier->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 4, 'unit_price' => '']],
    ])->assertRedirect();

    $quote = BtDocument::where('type', 'purchase_quote')->first();
    expect($quote)->not->toBeNull()->and($quote->number)->toBe('PQ-0001');

    $this->actingAs($user)->post("/dashboard/tools/documents/{$quote->id}/convert")->assertRedirect();
    $order = BtDocument::where('type', 'purchase_order')->first();
    expect($order)->not->toBeNull()->and($order->number)->toBe('PO-0001');

    $this->actingAs($user)->post("/dashboard/tools/documents/{$order->id}/convert")->assertRedirect();

    $bill = BtDocument::where('type', 'purchase_invoice')->first();
    expect($bill)->not->toBeNull()
        ->and($bill->number)->toBe('PB-0001')
        ->and($bill->status)->toBe('unpaid')
        // cost price is used for purchase lines
        ->and($bill->total)->toBe(4 * 5000)
        ->and($item->fresh()->stock)->toBe(4)
        ->and(JournalEntry::where('source_type', 'bt_document')->where('source_id', $bill->id)->exists())->toBeTrue();
});

test('crediting an invoice records a credit note and reduces the balance', function () {
    $user = accUser();
    $customer = BtContact::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'kind' => BtContact::CUSTOMER, 'name' => 'Buyer',
    ]);
    $item = accItem($user, 20);

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 2, 'unit_price' => '']],
    ])->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();
    expect($invoice->total)->toBe(16000);

    $this->actingAs($user)->post('/dashboard/tools/credit_notes', [
        'issue_date' => now()->toDateString(),
        'invoice_id' => $invoice->id,
        'amount' => 6000,
    ])->assertRedirect();

    expect($invoice->fresh()->paid_amount)->toBe(6000)
        ->and($invoice->fresh()->status)->toBe('partial')
        ->and(BtDocument::where('type', 'credit_note')->count())->toBe(1);
});

test('creating an invoice writes a balanced journal entry', function () {
    $user = accUser();
    $customer = BtContact::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'kind' => BtContact::CUSTOMER, 'name' => 'Buyer',
    ]);
    $item = accItem($user, 20);

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'tax_rate' => 18,
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 3, 'unit_price' => '']],
    ])->assertRedirect();

    $entry = JournalEntry::with('lines')->latest('id')->first();
    expect($entry)->not->toBeNull();

    $debits = $entry->lines->sum('debit');
    $credits = $entry->lines->sum('credit');

    expect($debits)->toBe($credits)
        ->and($debits)->toBeGreaterThan(0);

    $invoice = BtDocument::where('type', 'invoice')->first();
    $this->actingAs($user)->delete("/dashboard/tools/documents/{$invoice->id}")->assertRedirect();

    expect(JournalEntry::count())->toBe(0)
        ->and($item->fresh()->stock)->toBe(20);
});

test('a quotation can convert straight to an invoice', function () {
    $user = accUser();
    $customer = BtContact::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'kind' => BtContact::CUSTOMER, 'name' => 'Buyer',
    ]);
    $item = accItem($user, 10);

    $this->actingAs($user)->post('/dashboard/tools/quotations', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 2, 'unit_price' => '']],
    ])->assertRedirect();

    $quote = BtDocument::where('type', 'quotation')->first();

    $this->actingAs($user)->post("/dashboard/tools/documents/{$quote->id}/convert", ['target' => 'invoice'])
        ->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toBe('INV-0001')
        ->and($invoice->converted_from_id)->toBe($quote->id)
        ->and($quote->fresh()->status)->toBe('accepted')
        ->and($item->fresh()->stock)->toBe(8)
        ->and(JournalEntry::where('source_type', 'bt_document')->count())->toBe(1);
});

test('an invoice converts to a delivery note without moving stock twice', function () {
    $user = accUser();
    $customer = BtContact::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'kind' => BtContact::CUSTOMER, 'name' => 'Buyer',
    ]);
    $item = accItem($user, 10);

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 4, 'unit_price' => '']],
    ])->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();
    expect($item->fresh()->stock)->toBe(6);

    $this->actingAs($user)->post("/dashboard/tools/documents/{$invoice->id}/convert", ['target' => 'delivery_note'])
        ->assertRedirect();

    $note = BtDocument::where('type', 'delivery_note')->first();
    expect($note)->not->toBeNull()
        ->and($note->number)->toBe('DN-0001')
        ->and($note->converted_from_id)->toBe($invoice->id)
        ->and($note->status)->toBe('posted')
        // Stock already left the warehouse with the invoice — the note must not repeat it.
        ->and($item->fresh()->stock)->toBe(6)
        ->and($invoice->fresh()->status)->toBe('unpaid')
        ->and(JournalEntry::where('source_type', 'bt_document')->count())->toBe(1);
});

test('duplicating an invoice issues a new invoice that posts its own entry', function () {
    $user = accUser();
    $customer = BtContact::create([
        'user_id' => $user->id, 'business_id' => $user->active_business_id,
        'kind' => BtContact::CUSTOMER, 'name' => 'Buyer',
    ]);
    $item = accItem($user, 10);

    $this->actingAs($user)->post('/dashboard/tools/invoices', [
        'contact_id' => $customer->id,
        'issue_date' => now()->toDateString(),
        'lines' => [['item_id' => $item->id, 'description' => '', 'qty' => 3, 'unit_price' => '']],
    ])->assertRedirect();

    $invoice = BtDocument::where('type', 'invoice')->first();
    expect($item->fresh()->stock)->toBe(7);

    $this->actingAs($user)->post("/dashboard/tools/documents/{$invoice->id}/clone")->assertRedirect();

    $copy = BtDocument::where('type', 'invoice')->where('id', '!=', $invoice->id)->first();
    expect($copy)->not->toBeNull()
        ->and($copy->number)->toBe('INV-0002')
        ->and($copy->total)->toBe($invoice->total)
        ->and($copy->status)->toBe('unpaid')
        ->and($item->fresh()->stock)->toBe(4)
        ->and(JournalEntry::where('source_type', 'bt_document')->count())->toBe(2);

    // Settlement documents are not cloneable.
    $this->actingAs($user)->post('/dashboard/tools/receipts', [
        'issue_date' => now()->toDateString(), 'invoice_id' => $invoice->id, 'amount' => 1000,
    ])->assertRedirect();

    $receipt = BtDocument::where('type', 'receipt')->first();
    $this->actingAs($user)->post("/dashboard/tools/documents/{$receipt->id}/clone")->assertNotFound();
});
