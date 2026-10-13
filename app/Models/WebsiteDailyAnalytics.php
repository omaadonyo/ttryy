<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteDailyAnalytics extends Model
{
    protected $fillable = [
        'hosted_website_id', 'date', 'uniques', 'visits', 'pageviews',
        'top_pages', 'sources', 'devices',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'uniques' => 'integer',
            'visits' => 'integer',
            'pageviews' => 'integer',
            'top_pages' => 'array',
            'sources' => 'array',
            'devices' => 'array',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(HostedWebsite::class, 'hosted_website_id');
    }
}
