<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('quotation_footer')->nullable()->after('receipt_footer');
            $table->string('invoice_footer')->nullable()->after('quotation_footer');
        });

        // Business Tools now works in US dollars by default.
        DB::table('businesses')->where('currency', 'UGX')->update(['currency' => 'USD']);
        DB::table('bt_settings')->where('currency', 'UGX')->update(['currency' => 'USD']);
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['quotation_footer', 'invoice_footer']);
        });
    }
};
