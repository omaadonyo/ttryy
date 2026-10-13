<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosted_websites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hosting_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('site_type')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('pending_setup')->index();
            $table->string('health_status', 20)->default('unknown');
            $table->string('service_state', 30)->default('active');
            $table->unsignedBigInteger('primary_domain_id')->nullable();
            $table->string('service_url')->nullable();
            $table->boolean('service_connected')->default(false);
            $table->string('service_version')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('last_command_at')->nullable();
            $table->timestamp('last_health_report_at')->nullable();
            $table->timestamp('last_health_check_at')->nullable();
            $table->timestamp('next_renewal_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('fail_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosted_websites');
    }
};
