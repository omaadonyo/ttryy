<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BtItem extends Model
{
    protected $table = 'bt_items';

    protected $fillable = [
        'user_id', 'business_id', 'name', 'sku', 'unit', 'cost_price', 'sale_price',
        'stock', 'reorder_level',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
            'reorder_level' => 'integer',
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

    public function moves(): HasMany
    {
        return $this->hasMany(BtStockMove::class, 'item_id');
    }

    public function isLow(): bool
    {
        return $this->stock <= $this->reorder_level;
    }

    public function stockValue(): int
    {
        return $this->stock * $this->cost_price;
    }
}
