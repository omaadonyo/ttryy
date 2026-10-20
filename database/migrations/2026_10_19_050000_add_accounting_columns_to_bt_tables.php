<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bt_documents', function (Blueprint $table) {
            $table->string('type', 24)->change();
            $table->foreignId('converted_from_id')->nullable()->after('invoice_id')->constrained('bt_documents')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->after('converted_from_id')->nullOnDelete();
        });

        Schema::table('bt_expenses', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('status')->nullOnDelete();
        });

        Schema::table('bt_document_lines', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('item_id')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bt_document_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_id');
        });

        Schema::table('bt_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
        });

        Schema::table('bt_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('converted_from_id');
            $table->dropConstrainedForeignId('bank_account_id');
        });
    }
};
