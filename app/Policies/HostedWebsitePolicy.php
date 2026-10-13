<?php

namespace App\Policies;

use App\Models\HostedWebsite;
use App\Models\User;

class HostedWebsitePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, HostedWebsite $website): bool
    {
        return $user->is_admin || $website->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, HostedWebsite $website): bool
    {
        return $user->is_admin || $website->user_id === $user->id;
    }

    public function delete(User $user, HostedWebsite $website): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $website->user_id === $user->id
            && in_array($website->status, [
                HostedWebsite::STATUS_PENDING,
                HostedWebsite::STATUS_FAILED,
                HostedWebsite::STATUS_CANCELLED,
            ], true);
    }

    public function requestSuspend(User $user, HostedWebsite $website): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $website->user_id === $user->id
            && $website->status === HostedWebsite::STATUS_ACTIVE;
    }

    public function manageService(User $user, ?HostedWebsite $website = null): bool
    {
        return (bool) $user->is_admin;
    }
}
