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
        Schema::create('employee_salary_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_salary_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12)->nullable();
            $table->decimal('percentage', 5)->nullable();
            $table->timestamps();

            $table->unique(['employee_salary_package_id', 'salary_component_id'], 'salary_package_component_unique');
            $table->index(['company_id', 'employee_salary_package_id'], 'salary_package_items_company_package_index');
            $table->index(['company_id', 'salary_component_id'], 'salary_package_items_company_component_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_package_items');
    }
};
