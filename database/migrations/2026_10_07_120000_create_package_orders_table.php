<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('package');
            $table->string('billing_frequency');
            $table->unsignedInteger('duration_months')->nullable();
            $table->unsignedInteger('periods')->default(1);
            $table->unsignedBigInteger('amount_per_period');
            $table->unsignedBigInteger('total_amount');
            $table->string('currency')->default('UGX');
            $table->string('status')->default('pending');
            $table->string('business_name');
            $table->string('phone');
            $table->string('niche')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_orders');
    }
};
