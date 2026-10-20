<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Business;

/**
 * The default chart of accounts every business starts with.
 *
 * Accounts are addressed by a stable "subtype" key so the ledger can post
 * without caring about the numbers a user later renames. `ensure()` is
 * idempotent — it only creates what is missing, so it is safe to call before
 * any posting or whenever the chart is opened.
 */
class ChartOfAccounts
{
    /**
     * code => [name, type, subtype, system]
     */
    public const DEFAULTS = [
        '1000' => ['Cash', Account::ASSET, Account::CASH, true],
        '1010' => ['Bank', Account::ASSET, Account::BANK, true],
        '1100' => ['Accounts Receivable', Account::ASSET, Account::RECEIVABLE, true],
        '1200' => ['Inventory', Account::ASSET, Account::INVENTORY, true],
        '1400' => ['Tax Receivable', Account::ASSET, Account::TAX_RECEIVABLE, true],
        '2000' => ['Accounts Payable', Account::LIABILITY, Account::PAYABLE, true],
        '2100' => ['Tax Payable', Account::LIABILITY, Account::TAX_PAYABLE, true],
        '2200' => ['Salaries Payable', Account::LIABILITY, Account::SALARIES_PAYABLE, true],
        '3000' => ["Owner's Equity", Account::EQUITY, Account::EQUITY_OPENING, true],
        '4000' => ['Sales Revenue', Account::INCOME, Account::REVENUE, true],
        '4100' => ['Sales Returns', Account::INCOME, Account::SALES_RETURNS, true],
        '5000' => ['Cost of Goods Sold', Account::EXPENSE, Account::COGS, true],
        '6000' => ['Salaries & Wages', Account::EXPENSE, Account::SALARIES, true],
        '6900' => ['Operating Expenses', Account::EXPENSE, Account::OPERATING_EXPENSE, true],
    ];

    /** Create any missing default accounts for the business. */
    public function ensure(Business $business): void
    {
        $existing = $business->accounts()->pluck('code')->all();

        foreach (self::DEFAULTS as $code => [$name, $type, $subtype, $system]) {
            if (in_array((string) $code, $existing, true)) {
                continue;
            }

            $business->accounts()->create([
                'user_id' => $business->owner_id,
                'code' => (string) $code,
                'name' => $name,
                'type' => $type,
                'subtype' => $subtype,
                'is_system' => $system,
            ]);
        }
    }

    /** Resolve a system account by its subtype key. */
    public function findBySubtype(Business $business, string $subtype): ?Account
    {
        return $business->accounts()->where('subtype', $subtype)->first();
    }

    /**
     * Find (or create) an operating-expense account for a free-text category,
     * so each expense category shows up in the chart and the trial balance.
     */
    public function expenseAccountFor(Business $business, string $category): Account
    {
        $category = trim($category) ?: 'General';

        $account = $business->accounts()
            ->where('type', Account::EXPENSE)
            ->where('name', $category)
            ->first();

        if ($account) {
            return $account;
        }

        $code = $this->nextCode($business, 6000, 6999);

        return $business->accounts()->create([
            'user_id' => $business->owner_id,
            'code' => $code,
            'name' => $category,
            'type' => Account::EXPENSE,
            'subtype' => Account::OPERATING_EXPENSE,
            'is_system' => false,
        ]);
    }

    /** First free numeric code in a range. */
    private function nextCode(Business $business, int $from, int $to): string
    {
        $taken = $business->accounts()->pluck('code')->all();

        for ($n = $from + 1; $n <= $to; $n++) {
            if (! in_array((string) $n, $taken, true)) {
                return (string) $n;
            }
        }

        return (string) $to;
    }
}
