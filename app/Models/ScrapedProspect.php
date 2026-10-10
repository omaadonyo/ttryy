<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapedProspect extends Model
{
    protected $fillable = [
        'niche', 'name', 'category', 'phone', 'address', 'district',
        'verified', 'source', 'source_url', 'hash', 'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'fetched_at' => 'datetime',
        ];
    }

    /** Shape served to the frontend. */
    public function toResultArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'phone' => $this->phone,
            'address' => $this->address,
            'district' => $this->district,
            'verified' => (bool) $this->verified,
            'fetched_at' => $this->fetched_at?->format('d M Y'),
        ];
    }
}
