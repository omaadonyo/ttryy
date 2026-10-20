<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cash wallet balance in UGX. Separate from token_balance: tokens
            // buy AI/group/search actions, this balance pays subscriptions.
            $table->unsignedBigInteger('balance_ugx')->default(0)->after('token_balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance_ugx');
        });
    }
};
