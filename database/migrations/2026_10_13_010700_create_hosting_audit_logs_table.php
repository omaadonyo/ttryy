<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type', 20)->default('system');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 60)->index();
            $table->foreignId('hosted_website_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->string('outcome', 20)->default('success');
            $table->string('reason')->nullable();
            $table->json('meta')->nullable();
            $table->uuid('operation_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_audit_logs');
    }
};
