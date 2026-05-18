<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('adds compound indexes for reviewed high traffic backend queries', function (string $table, string $index) {
    expect(collect(Schema::getIndexes($table)))
        ->pluck('name')
        ->toContain($index);
})->with([
    ['webhook_deliveries', 'webhook_deliveries_company_status_retry_index'],
    ['workflow_instances', 'workflow_instances_company_step_status_index'],
    ['workflow_instances', 'workflow_instances_company_subject_index'],
    ['subscription_invoices', 'subscription_invoices_company_status_due_index'],
    ['payroll_runs', 'payroll_runs_company_period_status_index'],
]);

it('keeps existing attendance and document expiry query indexes available', function (string $table, array $columns) {
    $indexColumns = collect(Schema::getIndexes($table))->pluck('columns')->all();

    expect($indexColumns)->toContain($columns);
})->with([
    ['attendance_records', ['company_id', 'employee_id', 'attendance_date']],
    ['attendance_records', ['company_id', 'status']],
    ['employee_documents', ['company_id', 'expiry_date']],
    ['company_documents', ['company_id', 'expiry_date']],
    ['journal_entries', ['company_id', 'entry_date']],
]);
