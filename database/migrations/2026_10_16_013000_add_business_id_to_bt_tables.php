<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tables that belong to a business. */
    private array $tables = ['bt_contacts', 'bt_items', 'bt_documents', 'bt_expenses', 'bt_counters', 'bt_stock_moves'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'business_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('business_id')->nullable()->after('user_id')->index();
                });
            }
        }

        $this->backfill();
    }

    /** Give every existing tools user a business and attach their rows to it. */
    private function backfill(): void
    {
        $userIds = collect();
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                $userIds = $userIds->merge(DB::table($table)->distinct()->pluck('user_id'));
            }
        }

        foreach ($userIds->filter()->unique() as $userId) {
            $setting = Schema::hasTable('bt_settings')
                ? DB::table('bt_settings')->where('user_id', $userId)->first()
                : null;
            $user = DB::table('users')->where('id', $userId)->first();

            $businessId = DB::table('businesses')->insertGetId([
                'owner_id' => $userId,
                'name' => $setting->business_name ?? ($user->name ?? 'My Business'),
                'phone' => $setting->phone ?? null,
                'email' => $setting->email ?? ($user->email ?? null),
                'address' => $setting->address ?? null,
                'currency' => $setting->currency ?? 'UGX',
                'tax_rate' => $setting->tax_rate ?? 0,
                'receipt_footer' => $setting->receipt_footer ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('business_user')->updateOrInsert(
                ['business_id' => $businessId, 'user_id' => $userId],
                ['role' => 'owner', 'created_at' => now(), 'updated_at' => now()],
            );

            foreach ($this->tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('user_id', $userId)->update(['business_id' => $businessId]);
                }
            }

            DB::table('users')->where('id', $userId)->update(['active_business_id' => $businessId]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'business_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('business_id');
                });
            }
        }
    }
};
