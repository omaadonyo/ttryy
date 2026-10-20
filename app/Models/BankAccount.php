<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    public const BANK = 'bank';

    public const CASH = 'cash';

    public const MOMO = 'momo';

    protected $fillable = [
        'business_id', 'user_id', 'account_id', 'name', 'type',
        'account_number', 'opening_balance', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function balance(): int
    {
        return (int) $this->opening_balance + (int) $this->transactions()->sum('amount');
    }
}
