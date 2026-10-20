<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 140);
            $table->text('body')->nullable();
            $table->string('status', 12)->default('new');
            $table->string('reply')->nullable();
            $table->unsignedInteger('votes')->default(0);
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('suggestion_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suggestion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['suggestion_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestion_votes');
        Schema::dropIfExists('suggestions');
    }
};
