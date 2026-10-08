<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->string('payment_method')->default('pending')->after('status');
            $table->string('tx_ref')->nullable()->after('payment_method');
            $table->unsignedBigInteger('paid_amount')->default(0)->after('tx_ref');
            $table->timestamp('paid_at')->nullable()->after('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'tx_ref', 'paid_amount', 'paid_at']);
        });
    }
};
