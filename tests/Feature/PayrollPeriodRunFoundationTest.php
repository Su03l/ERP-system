<?php

use App\Enums\PayrollPeriodStatus;
use App\Enums\PayrollRunStatus;
use App\Models\Company;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\User;
use App\Models\WorkflowInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('stores tenant scoped payroll periods with localized names and status casts', function () {
    $company = Company::factory()->create();
    $closer = User::factory()->create(['company_id' => $company->id]);

    $period = PayrollPeriod::factory()->create([
        'company_id' => $company->id,
        'name_ar' => 'رواتب يناير 2026',
        'name_en' => 'January 2026 Payroll',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-01-31',
        'pay_date' => '2026-02-05',
        'status' => PayrollPeriodStatus::Open,
        'closed_by' => $closer->id,
        'closed_at' => now(),
    ]);

    expect($period->company->is($company))->toBeTrue()
        ->and($company->payrollPeriods()->whereKey($period)->exists())->toBeTrue()
        ->and($period->closedBy->is($closer))->toBeTrue()
        ->and($period->status)->toBe(PayrollPeriodStatus::Open)
        ->and($period->starts_on->toDateString())->toBe('2026-01-01')
        ->and($period->ends_on->toDateString())->toBe('2026-01-31')
        ->and($period->pay_date->toDateString())->toBe('2026-02-05');
});

it('prevents overlapping payroll periods inside the same company', function () {
    $company = Company::factory()->create();

    PayrollPeriod::factory()->create([
        'company_id' => $company->id,
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-01-31',
    ]);

    expect(fn () => PayrollPeriod::factory()->create([
        'company_id' => $company->id,
        'starts_on' => '2026-01-15',
        'ends_on' => '2026-02-14',
    ]))->toThrow(ValidationException::class);
});

it('allows matching date ranges in different companies', function () {
    PayrollPeriod::factory()->create([
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-01-31',
    ]);

    $otherCompanyPeriod = PayrollPeriod::factory()->create([
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-01-31',
    ]);

    expect($otherCompanyPeriod)->toBeInstanceOf(PayrollPeriod::class);
});

it('stores payroll runs with safe decimal defaults and approval relationships', function () {
    $company = Company::factory()->create();
    $period = PayrollPeriod::factory()->create([
        'company_id' => $company->id,
        'starts_on' => '2026-03-01',
        'ends_on' => '2026-03-31',
    ]);
    $generator = User::factory()->create(['company_id' => $company->id]);
    $approver = User::factory()->create(['company_id' => $company->id]);
    $workflowInstance = WorkflowInstance::factory()->create(['company_id' => $company->id]);

    $run = PayrollRun::factory()->create([
        'company_id' => $company->id,
        'payroll_period_id' => $period->id,
        'run_number' => 'PAY-2026-03',
        'status' => PayrollRunStatus::Approved,
        'total_employees' => 25,
        'gross_amount' => 125000,
        'total_allowances' => 15000,
        'total_deductions' => 5000,
        'net_amount' => 135000,
        'generated_by' => $generator->id,
        'generated_at' => now(),
        'approved_by' => $approver->id,
        'approved_at' => now(),
        'workflow_instance_id' => $workflowInstance->id,
    ]);

    expect($run->company->is($company))->toBeTrue()
        ->and($company->payrollRuns()->whereKey($run)->exists())->toBeTrue()
        ->and($period->payrollRuns()->whereKey($run)->exists())->toBeTrue()
        ->and($run->payrollPeriod->is($period))->toBeTrue()
        ->and($run->generatedBy->is($generator))->toBeTrue()
        ->and($run->approvedBy->is($approver))->toBeTrue()
        ->and($run->workflowInstance->is($workflowInstance))->toBeTrue()
        ->and($run->status)->toBe(PayrollRunStatus::Approved)
        ->and($run->gross_amount)->toBe('125000.00')
        ->and($run->net_amount)->toBe('135000.00');
});

it('provides payroll period and run status options', function () {
    app()->setLocale('en');

    expect(PayrollPeriodStatus::values())->toContain('draft', 'open', 'closed', 'cancelled')
        ->and(PayrollRunStatus::values())->toContain('draft', 'processing', 'approved', 'paid')
        ->and(PayrollPeriodStatus::Open->label())->toBe('Open')
        ->and(PayrollRunStatus::PendingApproval->label())->toBe('Pending approval');
});
