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
        'duration_months',
        'periods',
        'amount_per_period',
        'total_amount',
        'currency',
        'status',
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
            'total_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
