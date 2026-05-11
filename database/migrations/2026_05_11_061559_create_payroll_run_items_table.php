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
        Schema::create('payroll_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic_salary', 12);
            $table->decimal('gross_salary', 12);
            $table->decimal('total_allowances', 12)->default(0);
            $table->decimal('total_deductions', 12)->default(0);
            $table->decimal('net_salary', 12);
            $table->decimal('attendance_deduction', 12)->nullable();
            $table->decimal('leave_deduction', 12)->nullable();
            $table->decimal('overtime_amount', 12)->nullable();
            $table->string('status')->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
            $table->index(['company_id', 'payroll_run_id']);
            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_run_items');
    }
};
