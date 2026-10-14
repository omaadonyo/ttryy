<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BtDocumentLine extends Model
{
    protected $table = 'bt_document_lines';

    protected $fillable = [
        'document_id', 'item_id', 'description', 'qty',
        'unit_price', 'unit_cost', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'float',
            'unit_price' => 'integer',
            'unit_cost' => 'integer',
            'line_total' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(BtDocument::class, 'document_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(BtItem::class, 'item_id');
    }
}
