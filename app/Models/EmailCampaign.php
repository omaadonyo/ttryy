<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $fillable = [
        'user_id', 'subject', 'body', 'status', 'total', 'sent', 'delivered', 'failed',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'sent' => 'integer',
            'delivered' => 'integer',
            'failed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function readCount(): int
    {
        return $this->recipients()->whereNotNull('opened_at')->count();
    }

    /** Opens grouped by day for charts: [['label' => '12 Oct', 'opens' => 3], ...]. */
    public function opensByDay(): array
    {
        return $this->recipients()
            ->whereNotNull('opened_at')
            ->selectRaw('DATE(opened_at) as day, COUNT(*) as opens')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($r) => [
                'label' => \Carbon\Carbon::parse($r->day)->format('d M'),
                'opens' => (int) $r->opens,
            ])->all();
    }
}
