<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    public const FULL_TIME = 'full_time';

    public const PART_TIME = 'part_time';

    public const CONTRACT = 'contract';

    public const MONTHLY = 'monthly';

    public const WEEKLY = 'weekly';

    public const DAILY = 'daily';

    protected $fillable = [
        'business_id', 'user_id', 'name', 'email', 'phone', 'position',
        'employment_type', 'base_salary', 'pay_frequency', 'hired_on',
        'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'integer',
            'hired_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }
}
