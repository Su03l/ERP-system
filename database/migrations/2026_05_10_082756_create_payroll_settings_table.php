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
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('payroll_cycle_type')->default('monthly');
            $table->unsignedTinyInteger('default_pay_day')->default(1);
            $table->boolean('overtime_calculation_enabled')->default(true);
            $table->boolean('absence_deduction_enabled')->default(true);
            $table->boolean('late_deduction_enabled')->default(true);
            $table->string('default_currency', 3)->default('SAR');
            $table->string('payslip_language')->default('ar');
            $table->boolean('payroll_approval_required')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('company_id');
            $table->index(['company_id', 'payroll_cycle_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
