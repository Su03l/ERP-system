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
        Schema::create('payroll_run_item_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->decimal('amount', 12);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['payroll_run_item_id', 'type']);
            $table->index('salary_component_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_run_item_components');
    }
};
