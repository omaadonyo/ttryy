<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $suspended_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'active_business_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'token_balance' => 'integer',
            'balance_ugx' => 'integer',
            'suspended_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /** Businesses this user is a member of (owner or staff). */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class)->withPivot('role')->withTimestamps();
    }

    /** Businesses this user owns. */
    public function ownedBusinesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function activeBusiness(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'active_business_id');
    }

    /**
     * A suspended account is blocked from the dashboard until an
     * administrator reactivates it.
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /**
     * Package orders placed by the user.
     *
     * @return HasMany<PackageOrder, $this>
     */
    public function packageOrders(): HasMany
    {
        return $this->hasMany(PackageOrder::class);
    }

    /**
     * Saved prospect contacts scraped by the user.
     *
     * @return HasMany<SavedContact, $this>
     */
    public function savedContacts(): HasMany
    {
        return $this->hasMany(SavedContact::class);
    }

    /**
     * Websites this client owns on the hosting platform.
     *
     * @return HasMany<HostedWebsite, $this>
     */
    public function hostedWebsites(): HasMany
    {
        return $this->hasMany(HostedWebsite::class);
    }

    /**
     * @return HasMany<TokenTopup, $this>
     */
    public function tokenTopups(): HasMany
    {
        return $this->hasMany(TokenTopup::class);
    }

    /**
     * @return HasMany<TokenTransaction, $this>
     */
    public function tokenTransactions(): HasMany
    {
        return $this->hasMany(TokenTransaction::class);
    }

    /**
     * Cash wallet ledger (top-ups, order payments, refunds).
     *
     * @return HasMany<WalletTransaction, $this>
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * @return HasMany<WalletTopup, $this>
     */
    public function walletTopups(): HasMany
    {
        return $this->hasMany(WalletTopup::class);
    }

    /**
     * @return HasMany<OrderPayment, $this>
     */
    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    /**
     * @return HasMany<MessageTemplate, $this>
     */
    public function messageTemplates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    /**
     * @return HasMany<GroupUnlock, $this>
     */
    public function groupUnlocks(): HasMany
    {
        return $this->hasMany(GroupUnlock::class);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
