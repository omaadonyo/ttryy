<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    public const CREDIT = 'credit';

    public const DEBIT = 'debit';

    protected $fillable = [
        'business_id', 'bank_account_id', 'user_id', 'date', 'description',
        'amount', 'type', 'reference', 'source_type', 'source_id',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'integer'];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
