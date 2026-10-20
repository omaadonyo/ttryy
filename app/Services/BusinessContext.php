<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use App\Services\Accounting\ChartOfAccounts;

/**
 * Resolves the business a user is currently working in, and creates a
 * default one on first use. Businesses are the tenant boundary for
 * Business Tools — all data is scoped to a business, and staff share it.
 */
class BusinessContext
{
    /** The business the user can see, preferring their active choice. */
    public function forUser(User $user): Business
    {
        $accessible = $user->businesses()->pluck('businesses.id');

        if ($user->active_business_id && $accessible->contains($user->active_business_id)) {
            return Business::find($user->active_business_id);
        }

        if ($accessible->isNotEmpty()) {
            $business = Business::find($accessible->first());
            $user->forceFill(['active_business_id' => $business->id])->save();

            return $business;
        }

        return $this->create($user);
    }

    public function create(User $user, ?string $name = null): Business
    {
        $business = Business::create([
            'owner_id' => $user->id,
            'name' => $name ?: trim($user->name."'s Business"),
            'email' => $user->email,
        ]);

        $business->users()->attach($user->id, ['role' => 'owner']);
        $user->forceFill(['active_business_id' => $business->id])->save();

        app(ChartOfAccounts::class)->ensure($business);

        return $business;
    }

    public function userCanAccess(User $user, Business $business): bool
    {
        return $user->businesses()->whereKey($business->id)->exists();
    }
}
