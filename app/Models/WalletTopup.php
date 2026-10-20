<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTopup extends Model
{
    protected $fillable = [
        'user_id', 'reference', 'amount_ugx', 'method', 'status',
        'gateway_tx_id', 'tx_ref', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_ugx' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
