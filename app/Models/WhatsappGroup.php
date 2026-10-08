<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappGroup extends Model
{
    protected $fillable = [
        'name', 'niche', 'description', 'invite_link',
        'member_count', 'token_cost', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'member_count' => 'integer',
            'token_cost' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(GroupUnlock::class);
    }

    public function unlockedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->unlocks()->where('user_id', $user->id)->exists();
    }
}
