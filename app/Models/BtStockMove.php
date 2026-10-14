<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BtStockMove extends Model
{
    protected $table = 'bt_stock_moves';

    protected $fillable = [
        'user_id', 'item_id', 'qty_change', 'reason', 'ref_type', 'ref_id',
    ];

    protected function casts(): array
    {
        return ['qty_change' => 'integer'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(BtItem::class, 'item_id');
    }
}
