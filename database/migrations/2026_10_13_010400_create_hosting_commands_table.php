<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_commands', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Operation ID; doubles as replay nonce');
            $table->foreignId('hosted_website_id')->constrained()->cascadeOnDelete();
            $table->string('action', 30);
            $table->string('state', 20)->default('pending');
            $table->string('actor_type', 20)->default('system');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->text('error')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();

            $table->index(['hosted_website_id', 'state']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_commands');
    }
};
