<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageOrder extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'package',
        'billing_frequency',
        'domain',
        'duration_months',
        'periods',
        'amount_per_period',
        'domain_fee',
        'total_amount',
        'due_today',
        'currency',
        'status',
        'payment_method',
        'tx_ref',
        'paid_amount',
        'paid_at',
        'business_name',
        'phone',
        'niche',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'duration_months' => 'integer',
            'periods' => 'integer',
            'amount_per_period' => 'integer',
            'paid_amount' => 'integer',
            'paid_at' => 'datetime',
            'total_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
