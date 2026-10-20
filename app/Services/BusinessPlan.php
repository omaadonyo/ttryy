<?php

namespace App\Services;

use App\Models\BtDocument;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Business Tools plans.
 *
 * Every business starts on the free plan: one business, two team members,
 * three products and a single document of each type. Premium lifts every cap
 * and unlocks the reporting suite. Premium is bought from the cash wallet
 * (UGX 800 a day or UGX 10,000 a month) or granted by an admin.
 */
class BusinessPlan
{
    public const FREE = 'free';

    public const PREMIUM = 'premium';

    /** Price in UGX per billing cycle. */
    public const PRICES = [
        BusinessSubscription::DAILY => 800,
        BusinessSubscription::MONTHLY => 10000,
    ];

    /** Free-plan caps — the ceiling before an upgrade is required. */
    public const FREE_MAX_BUSINESSES = 1;

    public const FREE_MAX_STAFF = 2;

    public const FREE_MAX_ITEMS = 3;

    public const FREE_MAX_DOCS = 1;

    /** Sections that stay locked (blurred) until a business goes premium. */
    public const PREMIUM_TABS = ['reports', 'cashflow', 'pnl', 'balancesheet', 'activity'];

    public static function isPremium(?Business $business): bool
    {
        return $business?->isPremium() ?? false;
    }

    public static function price(string $cycle): int
    {
        return self::PRICES[$cycle] ?? self::PRICES[BusinessSubscription::MONTHLY];
    }

    /**
     * The current plan's ceilings. A null value means unlimited.
     *
     * @return array{businesses: ?int, staff: ?int, items: ?int, documents: ?int}
     */
    public static function limits(?Business $business): array
    {
        $premium = self::isPremium($business);

        return [
            'businesses' => $premium ? null : self::FREE_MAX_BUSINESSES,
            'staff' => $premium ? null : self::FREE_MAX_STAFF,
            'items' => $premium ? null : self::FREE_MAX_ITEMS,
            'documents' => $premium ? null : self::FREE_MAX_DOCS * count(BtDocument::TYPES),
        ];
    }

    /**
     * @return array{businesses: int, staff: int, items: int, documents: int}
     */
    public static function usage(Business $business): array
    {
        return [
            'businesses' => Business::where('owner_id', $business->owner_id)->count(),
            'staff' => $business->users()->count(),
            'items' => $business->items()->count(),
            'documents' => $business->documents()->count(),
        ];
    }

    /**
     * Buy or extend premium. When $charge is true the price is taken from the
     * buyer's wallet; an admin grant passes false and records who granted it.
     */
    public static function subscribe(Business $business, User $buyer, string $cycle, ?User $admin = null, bool $charge = true): BusinessSubscription
    {
        $cycle = array_key_exists($cycle, self::PRICES) ? $cycle : BusinessSubscription::MONTHLY;
        $price = $charge ? self::price($cycle) : 0;

        return DB::transaction(function () use ($business, $buyer, $cycle, $admin, $charge, $price) {
            if ($charge) {
                CashWallet::debit($buyer, $price, 'Business Tools premium ('.$cycle.') — '.$business->name, 'subscription');
            }

            // Extend from the existing expiry so renewals never lose time.
            $base = $business->premium_until && $business->premium_until->isFuture()
                ? $business->premium_until->copy()
                : now();

            $endsAt = $cycle === BusinessSubscription::DAILY
                ? $base->addDay()
                : $base->addMonth();

            $subscription = $business->subscriptions()->create([
                'user_id' => $buyer->id,
                'granted_by' => $admin?->id,
                'cycle' => $cycle,
                'amount' => $price,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'status' => BusinessSubscription::ACTIVE,
                'source' => $admin ? 'admin' : 'wallet',
            ]);

            $business->forceFill([
                'plan' => self::PREMIUM,
                'premium_until' => $endsAt,
            ])->save();

            return $subscription;
        });
    }

    public static function revoke(Business $business): void
    {
        DB::transaction(function () use ($business) {
            $business->subscriptions()->where('status', BusinessSubscription::ACTIVE)
                ->update(['status' => BusinessSubscription::EXPIRED]);

            $business->forceFill(['plan' => self::FREE, 'premium_until' => null])->save();
        });
    }
}
