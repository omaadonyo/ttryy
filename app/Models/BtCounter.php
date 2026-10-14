<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BtCounter extends Model
{
    protected $table = 'bt_counters';

    protected $fillable = ['user_id', 'type', 'next_no'];

    /**
     * Next document number, e.g. INV-0042. Safe under concurrency
     * via a row lock inside a transaction.
     */
    public static function nextNo(int $userId, string $type, string $prefix): string
    {
        return DB::transaction(function () use ($userId, $type, $prefix) {
            $counter = static::where('user_id', $userId)->where('type', $type)
                ->lockForUpdate()->first();

            if (! $counter) {
                $counter = static::create(['user_id' => $userId, 'type' => $type, 'next_no' => 1]);
            }

            $no = $counter->next_no;
            $counter->increment('next_no');

            return sprintf('%s-%04d', $prefix, $no);
        });
    }
}
