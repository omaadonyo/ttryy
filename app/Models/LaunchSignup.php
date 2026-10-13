<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaunchSignup extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'name', 'phone', 'email', 'idea_name', 'niche', 'idea',
        'status', 'user_id', 'admin_note',
        'payment_status', 'gateway_tx_id', 'paid_amount', 'paid_at',
    ];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
