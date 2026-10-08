<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupUnlock extends Model
{
    protected $fillable = [
        'user_id', 'whatsapp_group_id', 'tokens_charged',
    ];

    protected function casts(): array
    {
        return ['tokens_charged' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(WhatsappGroup::class, 'whatsapp_group_id');
    }
}
