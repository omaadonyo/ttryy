<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HostingAuditLog extends Model
{
    protected $fillable = [
        'actor_type', 'actor_id', 'action', 'hosted_website_id', 'client_id',
        'outcome', 'reason', 'meta', 'operation_id', 'ip',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(HostedWebsite::class, 'hosted_website_id');
    }

    /**
     * Record a safe audit event. Never pass secrets, passwords or tokens in $meta.
     */
    public static function log(string $action, array $data = []): self
    {
        return static::create([
            'actor_type' => $data['actor_type'] ?? 'system',
            'actor_id' => $data['actor_id'] ?? null,
            'action' => $action,
            'hosted_website_id' => $data['hosted_website_id'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'outcome' => $data['outcome'] ?? 'success',
            'reason' => $data['reason'] ?? null,
            'meta' => $data['meta'] ?? null,
            'operation_id' => $data['operation_id'] ?? (string) Str::uuid(),
            'ip' => $data['ip'] ?? request()?->ip(),
        ]);
    }
}
