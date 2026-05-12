<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number');
            $table->string('vendor_invoice_number')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->text('notes_ar')->nullable();
            $table->text('notes_en')->nullable();
            $table->foreignId('posted_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'vendor_id']);
            $table->index(['company_id', 'vendor_invoice_number']);
            $table->index(['company_id', 'invoice_date']);
            $table->index(['company_id', 'due_date']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'posted_journal_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
