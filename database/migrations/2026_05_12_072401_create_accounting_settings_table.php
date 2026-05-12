<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('fiscal_year_start_month')->default(1);
            $table->string('default_currency', 3)->default('SAR');
            $table->boolean('tax_enabled')->default(false);
            $table->decimal('default_vat_rate', 5, 2)->default(0);
            $table->string('invoice_numbering_prefix')->default('INV');
            $table->string('journal_numbering_prefix')->default('JRN');
            $table->boolean('accounting_approval_required')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('company_id');
            $table->index(['company_id', 'tax_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_settings');
    }
};
