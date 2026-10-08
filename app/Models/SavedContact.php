<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedContact extends Model
{
    protected $fillable = [
        'user_id',
        'niche',
        'name',
        'type',
        'district',
        'contact',
        'phone',
        'need',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Phone digits only, for wa.me links. */
    public function waNumber(): string
    {
        return preg_replace('/\D+/', '', (string) $this->phone);
    }
}
