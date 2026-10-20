<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('number', 24);
            $table->date('period_start');
            $table->date('period_end');
            $table->bigInteger('gross')->default(0);
            $table->bigInteger('allowances')->default(0);
            $table->bigInteger('deductions')->default(0);
            $table->bigInteger('net')->default(0);
            $table->string('status', 12)->default('draft');
            $table->date('paid_on')->nullable();
            $table->foreignId('bank_account_id')->nullable()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
