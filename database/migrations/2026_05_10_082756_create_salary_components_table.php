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
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('code')->nullable();
            $table->string('type');
            $table->string('calculation_type');
            $table->decimal('default_amount', 12)->nullable();
            $table->decimal('default_percentage', 5)->nullable();
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_recurring')->default(true);
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};
