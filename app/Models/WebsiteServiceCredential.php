<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteServiceCredential extends Model
{
    protected $fillable = [
        'hosted_website_id', 'key_id', 'secret_hash', 'secret_encrypted',
        'revoked_at', 'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'revoked_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    protected $hidden = ['secret_encrypted', 'secret_hash'];

    public function website(): BelongsTo
    {
        return $this->belongsTo(HostedWebsite::class, 'hosted_website_id');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function fingerprint(): string
    {
        return 'sha256:'.substr((string) $this->secret_hash, 0, 12).'…';
    }
}
