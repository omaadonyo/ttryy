<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('price')->default(0);
            $table->string('deposit')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach (config('packages.packages', []) as $code => $p) {
            DB::table('hosting_plans')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $code,
                    'price' => (int) ($p['price'] ?? 0),
                    'deposit' => (string) ($p['deposit'] ?? ''),
                    'features' => json_encode([
                        'monthly' => $p['monthly'] ?? null,
                        'weekly' => $p['weekly'] ?? null,
                        'daily' => $p['daily'] ?? null,
                        'blurb' => $p['blurb'] ?? null,
                    ]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_plans');
    }
};
