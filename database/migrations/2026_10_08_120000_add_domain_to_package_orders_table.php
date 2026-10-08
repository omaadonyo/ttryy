<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->string('domain')->default('none')->after('billing_frequency');
            $table->unsignedBigInteger('domain_fee')->default(0)->after('amount_per_period');
            $table->unsignedBigInteger('due_today')->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->dropColumn(['domain', 'domain_fee', 'due_today']);
        });
    }
};
