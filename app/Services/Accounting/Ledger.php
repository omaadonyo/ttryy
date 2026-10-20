<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BtDocument;
use App\Models\BtExpense;
use App\Models\Business;
use App\Models\JournalEntry;
use App\Models\Payslip;

/**
 * A small double-entry posting engine.
 *
 * Every business action (invoice, receipt, bill, expense, payslip) posts a
 * balanced journal entry here. Posting is deliberately forgiving: if the
 * chart is missing an account or an entry would not balance it simply does
 * nothing, so a bookkeeping surprise can never break a sale. Entries carry a
 * source tag so they can be reversed when the source is deleted.
 */
class Ledger
{
    public const SOURCE_DOCUMENT = 'bt_document';

    public const SOURCE_EXPENSE = 'bt_expense';

    public const SOURCE_PAYSLIP = 'bt_payslip';

    public function __construct(private ChartOfAccounts $chart) {}

    /**
     * Write a balanced journal entry. Rows are
     * ['account' => Account|subtype, 'debit' => int, 'credit' => int, 'memo' => ?string].
     */
    public function post(
        Business $business,
        ?int $userId,
        string $date,
        string $memo,
        array $rows,
        string $sourceType,
        int $sourceId,
    ): ?JournalEntry {
        $this->chart->ensure($business);

        $resolved = [];

        foreach ($rows as $row) {
            $account = $row['account'] instanceof Account
                ? $row['account']
                : $this->chart->findBySubtype($business, (string) $row['account']);

            if (! $account) {
                return null;
            }

            $debit = (int) ($row['debit'] ?? 0);
            $credit = (int) ($row['credit'] ?? 0);

            if ($debit === 0 && $credit === 0) {
                continue;
            }

            $resolved[] = [
                'account_id' => $account->id,
                'debit' => $debit,
                'credit' => $credit,
                'memo' => $row['memo'] ?? null,
            ];
        }

        if (! $resolved) {
            return null;
        }

        if (array_sum(array_column($resolved, 'debit')) !== array_sum(array_column($resolved, 'credit'))) {
            return null;
        }

        $entry = $business->journalEntries()->create([
            'user_id' => $userId,
            'date' => $date,
            'reference' => $this->nextReference($business),
            'memo' => $memo,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);

        foreach ($resolved as $line) {
            $entry->lines()->create($line);
        }

        return $entry;
    }

    /** Remove every entry belonging to a source (used when it is deleted). */
    public function reverse(string $sourceType, int $sourceId): void
    {
        JournalEntry::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->get()
            ->each(fn (JournalEntry $entry) => $entry->delete());
    }

    /* ------------------------------ domain postings ------------------------------ */

    /** A sales invoice: Dr Accounts Receivable, Cr Revenue (and tax, and COGS). */
    public function postInvoice(BtDocument $doc): ?JournalEntry
    {
        $business = $this->business($doc);
        $taxable = max(0, (int) $doc->subtotal - (int) $doc->discount);

        $rows = [
            ['account' => Account::RECEIVABLE, 'debit' => (int) $doc->total],
            ['account' => Account::REVENUE, 'credit' => $taxable],
        ];

        if ((int) $doc->tax_amount > 0) {
            $rows[] = ['account' => Account::TAX_PAYABLE, 'credit' => (int) $doc->tax_amount];
        }

        $cost = (int) $doc->lines->sum(fn ($l) => $l->qty * $l->unit_cost);

        if ($cost > 0) {
            $rows[] = ['account' => Account::COGS, 'debit' => $cost];
            $rows[] = ['account' => Account::INVENTORY, 'credit' => $cost];
        }

        return $this->post($business, $doc->user_id, $doc->issue_date->toDateString(), 'Invoice '.$doc->number, $rows, self::SOURCE_DOCUMENT, $doc->id);
    }

    /** A purchase bill: Dr Inventory (and input tax), Cr Accounts Payable. */
    public function postPurchaseInvoice(BtDocument $doc): ?JournalEntry
    {
        $business = $this->business($doc);
        $taxable = max(0, (int) $doc->subtotal - (int) $doc->discount);

        $rows = [
            ['account' => Account::INVENTORY, 'debit' => $taxable],
        ];

        if ((int) $doc->tax_amount > 0) {
            $rows[] = ['account' => Account::TAX_RECEIVABLE, 'debit' => (int) $doc->tax_amount];
        }

        $rows[] = ['account' => Account::PAYABLE, 'credit' => (int) $doc->total];

        return $this->post($business, $doc->user_id, $doc->issue_date->toDateString(), 'Purchase invoice '.$doc->number, $rows, self::SOURCE_DOCUMENT, $doc->id);
    }

    /** A receipt, credit note or supplier payment. */
    public function postSettlement(BtDocument $doc): ?JournalEntry
    {
        $business = $this->business($doc);
        $amount = (int) $doc->total;
        $date = $doc->issue_date->toDateString();

        if ($doc->type === BtDocument::CREDIT_NOTE) {
            $rows = [
                ['account' => Account::SALES_RETURNS, 'debit' => $amount],
                ['account' => Account::RECEIVABLE, 'credit' => $amount],
            ];

            return $this->post($business, $doc->user_id, $date, 'Credit note '.$doc->number, $rows, self::SOURCE_DOCUMENT, $doc->id);
        }

        if ($doc->type === BtDocument::PURCHASE_PAYMENT) {
            $rows = [
                ['account' => Account::PAYABLE, 'debit' => $amount],
                ['account' => $this->moneyAccount($business, $doc->bank_account_id), 'credit' => $amount],
            ];

            return $this->post($business, $doc->user_id, $date, 'Supplier payment '.$doc->number, $rows, self::SOURCE_DOCUMENT, $doc->id);
        }

        $rows = [
            ['account' => $this->moneyAccount($business, $doc->bank_account_id), 'debit' => $amount],
            ['account' => Account::RECEIVABLE, 'credit' => $amount],
        ];

        return $this->post($business, $doc->user_id, $date, 'Receipt '.$doc->number, $rows, self::SOURCE_DOCUMENT, $doc->id);
    }

    /** An expense: Dr the category account, Cr Cash/Bank (paid) or Payable. */
    public function postExpense(BtExpense $expense): ?JournalEntry
    {
        $business = $this->business($expense);
        $this->chart->ensure($business);

        $account = $this->chart->expenseAccountFor($business, (string) $expense->category);
        $amount = (int) $expense->amount;

        $rows = [['account' => $account, 'debit' => $amount]];

        if ($expense->status === BtExpense::PAID) {
            $rows[] = ['account' => $this->moneyAccount($business, $expense->bank_account_id), 'credit' => $amount];
        } else {
            $rows[] = ['account' => Account::PAYABLE, 'credit' => $amount];
        }

        return $this->post($business, $expense->user_id, $expense->date->toDateString(), 'Expense '.$expense->category, $rows, self::SOURCE_EXPENSE, $expense->id);
    }

    /** A payslip: Dr Salaries, Cr Cash/Bank (paid) or Salaries Payable. */
    public function postPayslip(Payslip $payslip): ?JournalEntry
    {
        $business = $this->business($payslip);
        $amount = (int) $payslip->net;

        $rows = [['account' => Account::SALARIES, 'debit' => $amount]];

        if ($payslip->status === Payslip::PAID) {
            $rows[] = ['account' => $this->moneyAccount($business, $payslip->bank_account_id), 'credit' => $amount];
        } else {
            $rows[] = ['account' => Account::SALARIES_PAYABLE, 'credit' => $amount];
        }

        return $this->post($business, $payslip->user_id, ($payslip->paid_on ?? $payslip->period_end)->toDateString(), 'Payslip '.$payslip->number, $rows, self::SOURCE_PAYSLIP, $payslip->id);
    }

    /* ------------------------------ helpers ------------------------------ */

    private function business(BtDocument|BtExpense|Payslip $model): Business
    {
        return Business::findOrFail($model->business_id);
    }

    /** The asset account money moves through — a chosen bank, else cash. */
    private function moneyAccount(Business $business, ?int $bankAccountId): Account|string
    {
        if ($bankAccountId) {
            $bank = BankAccount::find($bankAccountId);
            if ($bank && $bank->account) {
                return $bank->account;
            }
        }

        return $this->chart->findBySubtype($business, Account::CASH)
            ?: Account::BANK;
    }

    private function nextReference(Business $business): string
    {
        return 'JE-'.str_pad((string) ($business->journalEntries()->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
