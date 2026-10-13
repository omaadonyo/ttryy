<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosted_website_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->string('type', 20)->default('custom');
            $table->boolean('is_primary')->default(false);
            $table->string('verification', 20)->default('unverified');
            $table->string('verification_token', 80)->nullable();
            $table->boolean('dns_ok')->default(false);
            $table->timestamp('dns_checked_at')->nullable();
            $table->string('ssl_status', 20)->default('none');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['hosted_website_id', 'is_primary']);
        });

        Schema::table('hosted_websites', function (Blueprint $table) {
            $table->foreign('primary_domain_id')->references('id')->on('website_domains')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hosted_websites', function (Blueprint $table) {
            $table->dropForeign(['primary_domain_id']);
        });

        Schema::dropIfExists('website_domains');
    }
};
