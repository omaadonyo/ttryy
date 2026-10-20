<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BtDocument extends Model
{
    protected $table = 'bt_documents';

    public const QUOTATION = 'quotation';

    public const SALES_ORDER = 'sales_order';

    public const DELIVERY_NOTE = 'delivery_note';

    public const INVOICE = 'invoice';

    public const CREDIT_NOTE = 'credit_note';

    public const RECEIPT = 'receipt';

    public const PURCHASE_QUOTE = 'purchase_quote';

    public const PURCHASE_ORDER = 'purchase_order';

    public const PURCHASE_INVOICE = 'purchase_invoice';

    public const PURCHASE_PAYMENT = 'purchase_payment';

    public const DRAFT = 'draft';

    public const SENT = 'sent';

    public const ACCEPTED = 'accepted';

    public const DECLINED = 'declined';

    public const FULFILLED = 'fulfilled';

    public const UNPAID = 'unpaid';

    public const PARTIAL = 'partial';

    public const PAID = 'paid';

    public const POSTED = 'posted';

    /** Prefix used when numbering each document type. */
    public const PREFIXES = [
        self::QUOTATION => 'QT',
        self::SALES_ORDER => 'SO',
        self::DELIVERY_NOTE => 'DN',
        self::INVOICE => 'INV',
        self::CREDIT_NOTE => 'CN',
        self::RECEIPT => 'RCT',
        self::PURCHASE_QUOTE => 'PQ',
        self::PURCHASE_ORDER => 'PO',
        self::PURCHASE_INVOICE => 'PB',
        self::PURCHASE_PAYMENT => 'PP',
    ];

    /** All recognised document types. */
    public const TYPES = [
        self::QUOTATION, self::SALES_ORDER, self::DELIVERY_NOTE, self::INVOICE,
        self::CREDIT_NOTE, self::RECEIPT, self::PURCHASE_QUOTE, self::PURCHASE_ORDER,
        self::PURCHASE_INVOICE, self::PURCHASE_PAYMENT,
    ];

    /** Sales documents flow out to customers; the rest come in from suppliers. */
    public const PURCHASE_TYPES = [
        self::PURCHASE_QUOTE, self::PURCHASE_ORDER, self::PURCHASE_INVOICE, self::PURCHASE_PAYMENT,
    ];

    /**
     * How each type behaves:
     *  - rolls  = a list document the SPA renders in the documents table
     *  - settle = settles a "bill" document (invoice/purchase invoice) balance
     *  - stock  = decrements (sale) or increments (purchase) inventory
     */
    public const STATUSES = [
        self::QUOTATION => [self::DRAFT, self::SENT, self::ACCEPTED, self::DECLINED],
        self::SALES_ORDER => [self::DRAFT, self::SENT, self::FULFILLED, self::DECLINED],
        self::DELIVERY_NOTE => [self::DRAFT, self::POSTED, self::FULFILLED],
        self::INVOICE => [self::UNPAID, self::PARTIAL, self::PAID],
        self::CREDIT_NOTE => [self::POSTED],
        self::RECEIPT => [self::POSTED],
        self::PURCHASE_QUOTE => [self::DRAFT, self::SENT, self::ACCEPTED, self::DECLINED],
        self::PURCHASE_ORDER => [self::DRAFT, self::SENT, self::FULFILLED, self::DECLINED],
        self::PURCHASE_INVOICE => [self::UNPAID, self::PARTIAL, self::PAID],
        self::PURCHASE_PAYMENT => [self::POSTED],
    ];

    protected $fillable = [
        'user_id', 'business_id', 'type', 'number', 'contact_id', 'issue_date', 'due_date',
        'valid_until', 'status', 'subtotal', 'discount', 'tax_rate',
        'tax_amount', 'total', 'paid_amount', 'invoice_id', 'payment_method',
        'notes', 'public_token', 'converted_from_id', 'bank_account_id',
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

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
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

    public function latestReceipt(): HasOne
    {
        return $this->hasOne(BtDocument::class, 'invoice_id')->latestOfMany('issue_date');
    }

    public function convertedFrom(): BelongsTo
    {
        return $this->belongsTo(BtDocument::class, 'converted_from_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(BtDocument::class, 'converted_from_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function isPurchase(): bool
    {
        return in_array($this->type, self::PURCHASE_TYPES, true);
    }

    /** Money owed on this document (invoices and purchase bills). */
    public function isBill(): bool
    {
        return in_array($this->type, [self::INVOICE, self::PURCHASE_INVOICE], true);
    }

    /** Documents that settle a bill's balance. */
    public function isSettlement(): bool
    {
        return in_array($this->type, [self::RECEIPT, self::CREDIT_NOTE, self::PURCHASE_PAYMENT], true);
    }

    /** The document each type is most naturally created from. */
    public function chainFrom(): ?string
    {
        return match ($this->type) {
            self::SALES_ORDER => self::QUOTATION,
            self::INVOICE => self::SALES_ORDER,
            self::DELIVERY_NOTE => self::INVOICE,
            self::CREDIT_NOTE => self::INVOICE,
            self::RECEIPT => self::INVOICE,
            self::PURCHASE_ORDER => self::PURCHASE_QUOTE,
            self::PURCHASE_INVOICE => self::PURCHASE_ORDER,
            self::PURCHASE_PAYMENT => self::PURCHASE_INVOICE,
            default => null,
        };
    }

    public function balance(): int
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->isBill()
            && in_array($this->status, [self::UNPAID, self::PARTIAL], true)
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    /** Recompute paid/unpaid/partial from paid_amount. */
    public function refreshInvoiceStatus(): void
    {
        if (! $this->isBill()) {
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
            self::SALES_ORDER => 'Sales Order',
            self::DELIVERY_NOTE => 'Delivery Note',
            self::INVOICE => 'Invoice',
            self::CREDIT_NOTE => 'Credit Note',
            self::RECEIPT => 'Receipt',
            self::PURCHASE_QUOTE => 'Purchase Quote',
            self::PURCHASE_ORDER => 'Purchase Order',
            self::PURCHASE_INVOICE => 'Purchase Invoice',
            self::PURCHASE_PAYMENT => 'Purchase Payment',
            default => ucfirst(str_replace('_', ' ', (string) $this->type)),
        };
    }
}
