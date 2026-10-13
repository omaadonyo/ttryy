<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteHealthCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hosted_website_id', 'status', 'http_status', 'response_ms',
        'ssl_ok', 'dns_ok', 'app_ok', 'db_ok', 'incident', 'checked_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'response_ms' => 'integer',
            'ssl_ok' => 'boolean',
            'dns_ok' => 'boolean',
            'app_ok' => 'boolean',
            'db_ok' => 'boolean',
            'incident' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(HostedWebsite::class, 'hosted_website_id');
    }
}
