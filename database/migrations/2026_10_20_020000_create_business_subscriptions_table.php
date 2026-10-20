<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cycle', 12);
            $table->bigInteger('amount')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 12)->default('active');
            $table->string('source', 16)->default('wallet');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_subscriptions');
    }
};
