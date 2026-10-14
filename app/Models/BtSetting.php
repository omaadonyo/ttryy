<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BtSetting extends Model
{
    protected $table = 'bt_settings';

    protected $fillable = [
        'user_id', 'business_name', 'phone', 'email', 'address',
        'currency', 'tax_rate', 'receipt_footer',
    ];

    protected function casts(): array
    {
        return ['tax_rate' => 'float'];
    }

    public static function for(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            ['currency' => 'UGX', 'tax_rate' => 0]
        );
    }
}
