<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            // How many billing periods the recorded payments have covered
            // so far. Used to pro-rate subscription renewals and to know
            // whether the one-time domain fee is already consumed.
            $table->unsignedInteger('periods_covered')->default(0)->after('periods');
        });
    }

    public function down(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->dropColumn('periods_covered');
        });
    }
};
