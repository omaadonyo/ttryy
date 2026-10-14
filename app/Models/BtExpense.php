<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BtExpense extends Model
{
    protected $table = 'bt_expenses';

    public const PAID = 'paid';

    public const UNPAID = 'unpaid';

    protected $fillable = [
        'user_id', 'date', 'category', 'amount', 'status', 'contact_id', 'note',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(BtContact::class, 'contact_id');
    }
}
