<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('payable');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_number');
            $table->date('payment_date');
            $table->string('direction');
            $table->string('method')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('SAR');
            $table->string('reference')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes_ar')->nullable();
            $table->text('notes_en')->nullable();
            $table->foreignId('posted_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'payment_number']);
            $table->index(['company_id', 'payment_date']);
            $table->index(['company_id', 'direction']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'vendor_id']);
            $table->index(['company_id', 'posted_journal_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
