<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteAnalyticsEvent extends Model
{
    protected $fillable = [
        'hosted_website_id', 'visitor_key', 'session_key', 'path',
        'referrer', 'source', 'device', 'country',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(HostedWebsite::class, 'hosted_website_id');
    }
}
