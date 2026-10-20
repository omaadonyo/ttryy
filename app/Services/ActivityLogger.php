<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes an audit trail entry: what action was performed, by whom, in
 * which business, from which IP address, and when.
 */
class ActivityLogger
{
    public function log(
        ?int $businessId,
        ?int $userId,
        string $action,
        string $description,
        ?Model $subject = null,
        ?string $ip = null,
        ?string $agent = null,
    ): ActivityLog {
        $request = request();

        return ActivityLog::create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip' => $ip ?? $request?->ip(),
            'user_agent' => $agent ?? ($request ? substr((string) $request->userAgent(), 0, 255) : null),
        ]);
    }
}
