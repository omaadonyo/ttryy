<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'phone', 'email', 'address',
        'currency', 'tax_rate', 'receipt_footer', 'quotation_footer', 'invoice_footer',
    ];

    protected $attributes = [
        'currency' => 'USD',
    ];

    protected function casts(): array
    {
        return ['tax_rate' => 'float'];
    }

    /** Alias so PDF/verify views can keep reading ->business_name. */
    protected function businessName(): Attribute
    {
        return Attribute::make(get: fn () => $this->name);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Everyone who can work in this business (owner + staff). */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(BtContact::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BtItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BtDocument::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(BtExpense::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }
}
