<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraped_prospects', function (Blueprint $table) {
            $table->id();
            $table->string('niche');
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('source')->default('yellow-pages');
            $table->string('source_url')->nullable();
            $table->string('hash')->unique();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
            $table->index(['niche', 'fetched_at']);
            $table->index(['name', 'category']);
        });

        Schema::table('saved_contacts', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
            $table->string('source')->default('sample')->after('email');
            $table->string('source_url')->nullable()->after('source');
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('draft');
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sent')->default(0);
            $table->unsignedInteger('delivered')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->timestamps();
        });

        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('token')->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::table('saved_contacts', function (Blueprint $table) {
            $table->dropColumn(['email', 'source', 'source_url']);
        });
        Schema::dropIfExists('scraped_prospects');
    }
};
