<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->boolean('credentials_handed_over')->default(false)->after('paid_at');
            $table->timestamp('handed_over_at')->nullable()->after('credentials_handed_over');
        });
    }

    public function down(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->dropColumn(['credentials_handed_over', 'handed_over_at']);
        });
    }
};
