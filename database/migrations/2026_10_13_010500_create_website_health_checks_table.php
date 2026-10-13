<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosted_website_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('unknown');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->unsignedInteger('response_ms')->nullable();
            $table->boolean('ssl_ok')->nullable();
            $table->boolean('dns_ok')->nullable();
            $table->boolean('app_ok')->nullable();
            $table->boolean('db_ok')->nullable();
            $table->boolean('incident')->default(false);
            $table->timestamp('checked_at')->useCurrent();
            $table->string('notes')->nullable();

            $table->index(['hosted_website_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_health_checks');
    }
};
