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

    /**
     * When the subscription period ends. The website stays running
     * while the plan is active and paid for.
     */
    public function expiresAt(): ?\Carbon\CarbonInterface
    {
        if (! $this->created_at) {
            return null;
        }

        return $this->created_at->copy()->addMonths($this->duration_months ?? 12);
    }

    public function totalDays(): int
    {
        if (! $this->created_at || ! $this->expiresAt()) {
            return 0;
        }

        return max(1, $this->created_at->diffInDays($this->expiresAt()));
    }

    public function daysLeft(): int
    {
        if (! $this->expiresAt()) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->expiresAt(), false));
    }

    public function progressPercent(): int
    {
        if ($this->totalDays() <= 0) {
            return 0;
        }

        $elapsed = $this->totalDays() - $this->daysLeft();

        return (int) min(100, max(0, round($elapsed / $this->totalDays() * 100)));
    }

    public function isActive(): bool
    {
        return $this->status === 'paid' && $this->expiresAt() !== null && now()->lt($this->expiresAt());
    }

    public function isExpired(): bool
    {
        return $this->expiresAt() !== null && now()->gte($this->expiresAt());
    }
}
