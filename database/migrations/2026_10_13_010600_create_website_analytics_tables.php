<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosted_website_id')->constrained()->cascadeOnDelete();
            $table->char('visitor_key', 64)->index();
            $table->char('session_key', 64);
            $table->string('path', 500)->default('/');
            $table->string('referrer', 500)->nullable();
            $table->string('source', 50)->nullable();
            $table->string('device', 20)->default('unknown');
            $table->char('country', 2)->nullable();
            $table->timestamps();

            $table->index(['hosted_website_id', 'created_at']);
        });

        Schema::create('website_daily_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hosted_website_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('uniques')->default(0);
            $table->unsignedInteger('visits')->default(0);
            $table->unsignedInteger('pageviews')->default(0);
            $table->json('top_pages')->nullable();
            $table->json('sources')->nullable();
            $table->json('devices')->nullable();
            $table->timestamps();

            $table->unique(['hosted_website_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_daily_analytics');
        Schema::dropIfExists('website_analytics_events');
    }
};
