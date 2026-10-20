<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    public const EMAIL = 'email';

    public const WHATSAPP = 'whatsapp';

    protected $fillable = [
        'business_id', 'document_id', 'user_id', 'channel',
        'recipient', 'message', 'sent_at',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(BtDocument::class, 'document_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
