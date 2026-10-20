<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSubscription extends Model
{
    public const DAILY = 'daily';

    public const MONTHLY = 'monthly';

    public const ACTIVE = 'active';

    public const EXPIRED = 'expired';

    protected $fillable = [
        'business_id', 'user_id', 'granted_by', 'cycle', 'amount',
        'starts_at', 'ends_at', 'status', 'source', 'note',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
