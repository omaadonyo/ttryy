<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapedProspect extends Model
{
    protected $fillable = [
        'niche', 'name', 'category', 'phone', 'address', 'district',
        'verified', 'source', 'source_url', 'detail_url', 'hash', 'fetched_at',
    ];

    /** Human-readable directory names for the `source` codes we store. */
    public const SOURCE_LABELS = [
        'yellow-pages' => 'Yellow Pages Uganda',
    ];

    public function sourceLabel(): string
    {
        return self::SOURCE_LABELS[$this->source] ?? ucfirst((string) $this->source);
    }

    /** Canonical "where this was found" link: listing detail first, directory page fallback. */
    public function sourceLink(): ?string
    {
        return $this->detail_url ?? $this->source_url;
    }

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
            'source' => $this->source,
            'source_label' => $this->sourceLabel(),
            'source_url' => $this->sourceLink(),
            'fetched_at' => $this->fetched_at?->format('d M Y'),
        ];
    }
}
