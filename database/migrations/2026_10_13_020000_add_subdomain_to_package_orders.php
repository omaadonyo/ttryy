<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->string('subdomain', 80)->nullable()->after('domain');
        });
    }

    public function down(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            $table->dropColumn('subdomain');
        });
    }
};
