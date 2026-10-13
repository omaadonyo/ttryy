<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_service_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosted_website_id')->constrained()->cascadeOnDelete();
            $table->string('key_id')->unique();
            $table->string('secret_hash', 128)->comment('SHA-256 fingerprint, safe to display truncated');
            $table->text('secret_encrypted')->comment('APP_KEY-encrypted secret for HMAC verification');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['hosted_website_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_service_credentials');
    }
};
