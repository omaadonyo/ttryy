<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('active_business_id')->nullable()->after('is_admin');
            $table->string('last_login_ip', 45)->nullable()->after('active_business_id');
            $table->timestamp('last_login_at')->nullable()->after('last_login_ip');
            $table->string('last_login_agent')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['active_business_id', 'last_login_ip', 'last_login_at', 'last_login_agent']);
        });
    }
};
