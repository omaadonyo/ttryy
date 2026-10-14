<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bt_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('currency', 8)->default('UGX');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('receipt_footer')->nullable();
            $table->timestamps();
        });

        Schema::create('bt_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->unsignedInteger('next_no')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'type']);
        });

        Schema::create('bt_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 10);
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'kind']);
        });

        Schema::create('bt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('unit', 12)->default('pcs');
            $table->bigInteger('cost_price')->default(0);
            $table->bigInteger('sale_price')->default(0);
            $table->integer('stock')->default(0);
            $table->integer('reorder_level')->default(5);
            $table->timestamps();
        });

        Schema::create('bt_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 12);
            $table->string('number', 24);
            $table->foreignId('contact_id')->nullable()->nullOnDelete();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('status', 12)->default('draft');
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('total')->default(0);
            $table->bigInteger('paid_amount')->default(0);
            $table->foreignId('invoice_id')->nullable()->nullOnDelete();
            $table->string('payment_method', 20)->nullable();
            $table->text('notes')->nullable();
            $table->string('public_token', 64)->unique();
            $table->timestamps();
            $table->unique(['user_id', 'type', 'number']);
            $table->index(['user_id', 'type', 'status']);
        });

        Schema::create('bt_document_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('bt_documents')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->nullOnDelete();
            $table->string('description');
            $table->decimal('qty', 12, 2)->default(1);
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('unit_cost')->default(0);
            $table->bigInteger('line_total')->default(0);
            $table->timestamps();
        });

        Schema::create('bt_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('category');
            $table->bigInteger('amount')->default(0);
            $table->string('status', 10)->default('paid');
            $table->foreignId('contact_id')->nullable()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'date']);
        });

        Schema::create('bt_stock_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('bt_items')->cascadeOnDelete();
            $table->integer('qty_change');
            $table->string('reason');
            $table->string('ref_type', 20)->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bt_stock_moves');
        Schema::dropIfExists('bt_expenses');
        Schema::dropIfExists('bt_document_lines');
        Schema::dropIfExists('bt_documents');
        Schema::dropIfExists('bt_items');
        Schema::dropIfExists('bt_contacts');
        Schema::dropIfExists('bt_counters');
        Schema::dropIfExists('bt_settings');
    }
};
