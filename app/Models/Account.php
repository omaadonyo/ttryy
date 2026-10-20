<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    public const ASSET = 'asset';

    public const LIABILITY = 'liability';

    public const EQUITY = 'equity';

    public const INCOME = 'income';

    public const EXPENSE = 'expense';

    public const TYPES = [self::ASSET, self::LIABILITY, self::EQUITY, self::INCOME, self::EXPENSE];

    /** Normal balance side for each account type. */
    public const NORMAL = [
        self::ASSET => 'debit',
        self::EXPENSE => 'debit',
        self::LIABILITY => 'credit',
        self::EQUITY => 'credit',
        self::INCOME => 'credit',
    ];

    /* System subtype keys — the ledger posts against these. */
    public const CASH = 'cash';

    public const BANK = 'bank';

    public const RECEIVABLE = 'receivable';

    public const PAYABLE = 'payable';

    public const INVENTORY = 'inventory';

    public const REVENUE = 'revenue';

    public const SALES_RETURNS = 'sales_returns';

    public const COGS = 'cogs';

    public const TAX_PAYABLE = 'tax_payable';

    public const TAX_RECEIVABLE = 'tax_receivable';

    public const SALARIES = 'salaries';

    public const SALARIES_PAYABLE = 'salaries_payable';

    public const OPERATING_EXPENSE = 'operating_expense';

    public const EQUITY_OPENING = 'equity_opening';

    protected $fillable = [
        'business_id', 'user_id', 'code', 'name', 'type', 'subtype',
        'is_system', 'is_active', 'opening_balance',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'opening_balance' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /** Signed balance in the account's normal direction. */
    public function signedBalance(int $debit = 0, int $credit = 0): int
    {
        $opening = self::NORMAL[$this->type] === 'debit' ? $this->opening_balance : -$this->opening_balance;

        return $opening + ($debit - $credit);
    }
}
