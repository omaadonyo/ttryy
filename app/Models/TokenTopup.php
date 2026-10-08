<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenTopup extends Model
{
    protected $fillable = [
        'user_id', 'reference', 'pack', 'tokens', 'amount_ugx',
        'status', 'tx_ref', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'tokens' => 'integer',
            'amount_ugx' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
