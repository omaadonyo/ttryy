<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebsiteDomain;

class WebsiteDomainPolicy
{
    public function view(User $user, WebsiteDomain $domain): bool
    {
        return $user->is_admin || $domain->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WebsiteDomain $domain): bool
    {
        return $user->is_admin || $domain->user_id === $user->id;
    }

    public function delete(User $user, WebsiteDomain $domain): bool
    {
        if ($user->is_admin) {
            return true;
        }

        if ($domain->user_id !== $user->id || $domain->is_primary) {
            return false;
        }

        return true;
    }
}
