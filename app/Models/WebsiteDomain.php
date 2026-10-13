<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteDomain extends Model
{
    public const TYPE_SUBDOMAIN = 'subdomain';

    public const TYPE_CUSTOM = 'custom';

    protected $fillable = [
        'hosted_website_id', 'user_id', 'domain', 'type', 'is_primary',
        'verification', 'verification_token', 'dns_ok', 'dns_checked_at',
        'ssl_status', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'dns_ok' => 'boolean',
            'dns_checked_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(HostedWebsite::class, 'hosted_website_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function url(): string
    {
        return 'https://'.$this->domain;
    }

    public function isVerified(): bool
    {
        if ($this->type === self::TYPE_SUBDOMAIN) {
            return true;
        }

        return $this->verification === 'verified';
    }
}
