<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            // A domain the customer already owns (captured when they choose
            // "No domain — I already have one"). The free subdomain label is
            // stored separately in `subdomain`.
            $table->string('custom_domain')->nullable()->after('subdomain');
        });
    }

    public function down(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->dropColumn('custom_domain');
        });
    }
};
