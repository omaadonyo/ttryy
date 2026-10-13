<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostedWebsite extends Model
{
    public const STATUS_PENDING = 'pending_setup';

    public const STATUS_PROVISIONING = 'provisioning';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROVISIONING,
        self::STATUS_ACTIVE,
        self::STATUS_SUSPENDED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
    ];

    public const HEALTH_UNKNOWN = 'unknown';

    public const HEALTH_HEALTHY = 'healthy';

    public const HEALTH_DEGRADED = 'degraded';

    public const HEALTH_UNAVAILABLE = 'unavailable';

    public const SERVICE_ACTIVE = 'active';

    public const SERVICE_SUSPENSION_PENDING = 'suspension_pending';

    public const SERVICE_SUSPENDED = 'suspended';

    public const SERVICE_RESTORATION_PENDING = 'restoration_pending';

    public const SERVICE_ERROR = 'error';

    protected $fillable = [
        'uuid', 'user_id', 'hosting_plan_id', 'name', 'site_type', 'description',
        'status', 'health_status', 'service_state', 'primary_domain_id',
        'service_url', 'service_connected', 'service_version',
        'last_heartbeat_at', 'last_command_at', 'last_health_report_at',
        'last_health_check_at', 'next_renewal_at', 'suspended_at',
        'suspension_reason', 'failed_at', 'fail_reason',
    ];

    protected function casts(): array
    {
        return [
            'service_connected' => 'boolean',
            'last_heartbeat_at' => 'datetime',
            'last_command_at' => 'datetime',
            'last_health_report_at' => 'datetime',
            'last_health_check_at' => 'datetime',
            'next_renewal_at' => 'datetime',
            'suspended_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(HostingPlan::class, 'hosting_plan_id');
    }

    public function domains(): HasMany
    {
        return $this->hasMany(WebsiteDomain::class);
    }

    public function primaryDomain(): BelongsTo
    {
        return $this->belongsTo(WebsiteDomain::class, 'primary_domain_id');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(WebsiteServiceCredential::class);
    }

    public function activeCredential(): ?WebsiteServiceCredential
    {
        return $this->credentials()->whereNull('revoked_at')->latest()->first();
    }

    public function commands(): HasMany
    {
        return $this->hasMany(HostingCommand::class);
    }

    public function healthChecks(): HasMany
    {
        return $this->hasMany(WebsiteHealthCheck::class);
    }

    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(WebsiteAnalyticsEvent::class);
    }

    public function dailyAnalytics(): HasMany
    {
        return $this->hasMany(WebsiteDailyAnalytics::class);
    }

    public function latestHealthCheck(): ?WebsiteHealthCheck
    {
        return $this->healthChecks()->latest('checked_at')->first();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PackageOrder::class, 'hosted_website_id');
    }

    public function isReachableByService(): bool
    {
        return $this->service_connected && ! empty($this->service_url) && $this->activeCredential() !== null;
    }

    public function heartbeatStale(): bool
    {
        if (! $this->last_heartbeat_at) {
            return true;
        }

        return $this->last_heartbeat_at->lt(now()->subMinutes((int) config('hosting.heartbeat_stale_minutes', 60)));
    }
}
