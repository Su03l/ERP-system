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
        Schema::table('webhook_deliveries', function (Blueprint $table): void {
            $table->index(['company_id', 'status', 'next_retry_at'], 'webhook_deliveries_company_status_retry_index');
        });

        Schema::table('workflow_instances', function (Blueprint $table): void {
            $table->index(['company_id', 'current_step_id', 'status'], 'workflow_instances_company_step_status_index');
            $table->index(['company_id', 'subject_type', 'subject_id'], 'workflow_instances_company_subject_index');
        });

        Schema::table('subscription_invoices', function (Blueprint $table): void {
            $table->index(['company_id', 'status', 'due_date'], 'subscription_invoices_company_status_due_index');
        });

        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->index(['company_id', 'payroll_period_id', 'status'], 'payroll_runs_company_period_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->dropIndex('payroll_runs_company_period_status_index');
        });

        Schema::table('subscription_invoices', function (Blueprint $table): void {
            $table->dropIndex('subscription_invoices_company_status_due_index');
        });

        Schema::table('workflow_instances', function (Blueprint $table): void {
            $table->dropIndex('workflow_instances_company_subject_index');
            $table->dropIndex('workflow_instances_company_step_status_index');
        });

        Schema::table('webhook_deliveries', function (Blueprint $table): void {
            $table->dropIndex('webhook_deliveries_company_status_retry_index');
        });
    }
};
