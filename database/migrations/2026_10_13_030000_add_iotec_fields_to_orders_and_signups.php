<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->string('gateway_tx_id')->nullable()->after('tx_ref');
        });

        Schema::table('launch_signups', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('status');
            $table->string('gateway_tx_id')->nullable()->after('payment_status');
            $table->integer('paid_amount')->nullable()->after('gateway_tx_id');
            $table->timestamp('paid_at')->nullable()->after('paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('launch_signups', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'gateway_tx_id', 'paid_amount', 'paid_at']);
        });

        Schema::table('package_orders', function (Blueprint $table) {
            $table->dropColumn('gateway_tx_id');
        });
    }
};
