<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    public const DRAFT = 'draft';

    public const PAID = 'paid';

    protected $fillable = [
        'business_id', 'user_id', 'employee_id', 'number', 'period_start', 'period_end',
        'gross', 'allowances', 'deductions', 'net', 'status', 'paid_on', 'bank_account_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'paid_on' => 'date',
            'gross' => 'integer',
            'allowances' => 'integer',
            'deductions' => 'integer',
            'net' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
