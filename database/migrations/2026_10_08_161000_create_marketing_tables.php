<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('niche')->nullable();
            $table->text('description')->nullable();
            $table->string('invite_link');
            $table->unsignedInteger('member_count')->default(0);
            $table->unsignedInteger('token_cost')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('group_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_group_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('tokens_charged');
            $table->timestamps();
            $table->unique(['user_id', 'whatsapp_group_id']);
        });

        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('group_unlocks');
        Schema::dropIfExists('whatsapp_groups');
    }
};
