<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageOrder extends Model
{
    protected $fillable = [
        'user_id',
        'hosted_website_id',
        'reference',        'package',
        'billing_frequency',
        'domain',
        'subdomain',
        'custom_domain',
        'duration_months',
        'periods',
        'periods_covered',
        'amount_per_period',
        'domain_fee',
        'total_amount',
        'due_today',
        'currency',
        'status',
        'payment_method',
        'tx_ref',
        'gateway_tx_id',
        'paid_amount',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'credentials_handed_over',
        'handed_over_at',
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
            'periods_covered' => 'integer',
            'amount_per_period' => 'integer',
            'paid_amount' => 'integer',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'refund_amount' => 'integer',
            'credentials_handed_over' => 'boolean',
            'handed_over_at' => 'datetime',
            'total_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Self-service payments made toward this order from the wallet or MoMo.
     *
     * @return HasMany<OrderPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    /**
     * The website this order provisioned, if any.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(HostedWebsite::class, 'hosted_website_id');
    }

    /**
     * When the subscription period ends. The website stays running
     * while the plan is active and paid for.
     */
    public function expiresAt(): ?CarbonInterface
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

    public function frequencyLabel(): string
    {
        return match ($this->billing_frequency) {
            'daily' => 'day',
            'weekly' => 'week',
            'monthly' => 'month',
            default => 'once',
        };
    }

    public function isDaily(): bool
    {
        return $this->billing_frequency === 'daily';
    }

    /**
     * Human rate label, e.g. "UGX 700/day", "UGX 21,000/month" or
     * "UGX 250,000 once" for a single upfront payment.
     */
    public function installmentLabel(): string
    {
        if ($this->billing_frequency === 'full') {
            return 'UGX '.number_format((int) $this->total_amount).' once';
        }

        return 'UGX '.number_format((int) $this->amount_per_period).'/'.$this->frequencyLabel();
    }

    /**
     * When the next installment is due, based on the periods already
     * covered since the order was placed. Null for upfront payments.
     */
    public function nextDueAt(): ?CarbonInterface
    {
        if (! $this->created_at || $this->billing_frequency === 'full') {
            return null;
        }

        $covered = max(0, (int) $this->periods_covered);

        return match ($this->billing_frequency) {
            'daily' => $this->created_at->copy()->addDays($covered),
            'weekly' => $this->created_at->copy()->addWeeks($covered),
            'monthly' => $this->created_at->copy()->addMonths($covered),
            default => null,
        };
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded' || $this->refunded_at !== null;
    }

    /**
     * Payment progress: how much of the order total is already paid,
     * and how much is left to complete the service payment.
     */
    public function amountRemaining(): int
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function paymentProgressPercent(): int
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return (int) min(100, max(0, round($this->paid_amount / $this->total_amount * 100)));
    }
}
