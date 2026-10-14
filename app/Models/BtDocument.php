<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BtDocument extends Model
{
    protected $table = 'bt_documents';

    public const QUOTATION = 'quotation';

    public const INVOICE = 'invoice';

    public const RECEIPT = 'receipt';

    public const DRAFT = 'draft';

    public const SENT = 'sent';

    public const ACCEPTED = 'accepted';

    public const DECLINED = 'declined';

    public const UNPAID = 'unpaid';

    public const PARTIAL = 'partial';

    public const PAID = 'paid';

    public const POSTED = 'posted';

    public const PREFIXES = [
        self::QUOTATION => 'QT',
        self::INVOICE => 'INV',
        self::RECEIPT => 'RCT',
    ];

    protected $fillable = [
        'user_id', 'type', 'number', 'contact_id', 'issue_date', 'due_date',
        'valid_until', 'status', 'subtotal', 'discount', 'tax_rate',
        'tax_amount', 'total', 'paid_amount', 'invoice_id', 'payment_method',
        'notes', 'public_token',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'tax_rate' => 'float',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'paid_amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(BtContact::class, 'contact_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BtDocumentLine::class, 'document_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BtDocument::class, 'invoice_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(BtDocument::class, 'invoice_id');
    }

    public function balance(): int
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->type === self::INVOICE
            && in_array($this->status, [self::UNPAID, self::PARTIAL], true)
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    /** Recompute paid/unpaid/partial from paid_amount. */
    public function refreshInvoiceStatus(): void
    {
        if ($this->type !== self::INVOICE) {
            return;
        }

        $this->status = $this->paid_amount >= $this->total && $this->total > 0
            ? self::PAID
            : ($this->paid_amount > 0 ? self::PARTIAL : self::UNPAID);
    }

    public function label(): string
    {
        return match ($this->type) {
            self::QUOTATION => 'Quotation',
            self::INVOICE => 'Invoice',
            default => 'Receipt',
        };
    }
}
