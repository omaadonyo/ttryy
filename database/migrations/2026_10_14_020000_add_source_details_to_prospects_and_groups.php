<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scraped_prospects', function (Blueprint $table) {
            $table->string('detail_url')->nullable()->after('source_url');
        });

        Schema::table('whatsapp_groups', function (Blueprint $table) {
            $table->string('source', 60)->nullable()->after('invite_link');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_groups', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('scraped_prospects', function (Blueprint $table) {
            $table->dropColumn('detail_url');
        });
    }
};
