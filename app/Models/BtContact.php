<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BtContact extends Model
{
    protected $table = 'bt_contacts';

    public const CUSTOMER = 'customer';

    public const SUPPLIER = 'supplier';

    protected $fillable = [
        'user_id', 'kind', 'name', 'phone', 'email', 'address', 'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BtDocument::class, 'contact_id');
    }

    /** Unpaid invoice balance owed by this customer. */
    public function balanceOwed(): int
    {
        return (int) $this->documents()
            ->where('type', BtDocument::INVOICE)
            ->whereIn('status', [BtDocument::UNPAID, BtDocument::PARTIAL])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as b')
            ->value('b');
    }
}
