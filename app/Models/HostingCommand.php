<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostingCommand extends Model
{
    public const ACTION_SUSPEND = 'suspend';

    public const ACTION_UNSUSPEND = 'unsuspend';

    public const ACTION_HEALTH_CHECK = 'health_check';

    public const ACTION_REPORT_STATUS = 'report_status';

    public const ACTION_PROVISION = 'provision';

    public const ACTIONS = [
        self::ACTION_SUSPEND,
        self::ACTION_UNSUSPEND,
        self::ACTION_HEALTH_CHECK,
        self::ACTION_REPORT_STATUS,
        self::ACTION_PROVISION,
    ];

    public const STATE_PENDING = 'pending';

    public const STATE_PROCESSING = 'processing';

    public const STATE_SUCCEEDED = 'succeeded';

    public const STATE_FAILED = 'failed';

    public const STATE_RETRY = 'retry';

    protected $fillable = [
        'uuid', 'hosted_website_id', 'action', 'state', 'actor_type', 'actor_id',
        'payload', 'response', 'error', 'attempts',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response' => 'array',
            'attempts' => 'integer',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(HostedWebsite::class, 'hosted_website_id');
    }

    public function isTerminal(): bool
    {
        return in_array($this->state, [self::STATE_SUCCEEDED, self::STATE_FAILED], true);
    }
}
