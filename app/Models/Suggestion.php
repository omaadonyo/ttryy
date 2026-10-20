<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Suggestion extends Model
{
    public const NEW = 'new';

    public const PLANNED = 'planned';

    public const SHIPPED = 'shipped';

    public const DECLINED = 'declined';

    public const STATUSES = [self::NEW, self::PLANNED, self::SHIPPED, self::DECLINED];

    protected $fillable = ['user_id', 'business_id', 'title', 'body', 'status', 'reply', 'votes'];

    protected function casts(): array
    {
        return ['votes' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Individual ballots — the running total lives in the votes column. */
    public function ballots(): HasMany
    {
        return $this->hasMany(SuggestionVote::class);
    }
}
