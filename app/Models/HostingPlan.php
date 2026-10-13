<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingPlan extends Model
{
    protected $fillable = [
        'code', 'name', 'price', 'deposit', 'features', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function websites(): HasMany
    {
        return $this->hasMany(HostedWebsite::class);
    }
}
