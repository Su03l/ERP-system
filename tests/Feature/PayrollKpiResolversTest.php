<?php

use App\DTOs\KpiDateRange;
use App\Enums\PayrollRunStatus;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use App\Services\Kpis\Payroll\AverageSalaryKpi;
use App\Services\Kpis\Payroll\LatestPayrollRunStatusKpi;
use App\Services\Kpis\Payroll\OvertimeCostKpi;
use App\Services\Kpis\Payroll\PayrollByDepartmentKpi;
use App\Services\Kpis\Payroll\TotalAllowancesKpi;
use App\Services\Kpis\Payroll\TotalDeductionsKpi;
use App\Services\Kpis\Payroll\TotalPayrollCostKpi;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves sensitive payroll KPI values inside the company and date range', function () {
    $company = Company::factory()->create();
    $department = Department::factory()->for($company)->create(['name_ar' => 'المالية']);
    $employee = Employee::factory()->for($company)->create(['department_id' => $department->id]);
    $secondEmployee = Employee::factory()->for($company)->create(['department_id' => $department->id]);
    $run = PayrollRun::factory()->for($company)->create([
        'status' => PayrollRunStatus::Approved,
        'run_number' => 'PAY-001',
        'generated_at' => '2026-01-20 10:00:00',
    ]);
    PayrollRunItem::factory()->for($company)->for($run, 'payrollRun')->for($secondEmployee, 'employee')->create([
        'net_salary' => 1000,
        'total_allowances' => 200,
        'total_deductions' => 50,
        'overtime_amount' => 25,
    ]);
    PayrollRunItem::factory()->for($company)->for($run, 'payrollRun')->for($employee)->create([
        'net_salary' => 500,
        'total_allowances' => 100,
        'total_deductions' => 25,
        'overtime_amount' => 10,
    ]);

    $range = KpiDateRange::fromDates('2026-01-01', '2026-01-31');

    expect(app(TotalPayrollCostKpi::class)->resolve($company, $range)->value)->toBe(1500.0)
        ->and(app(AverageSalaryKpi::class)->resolve($company, $range)->value)->toBe(750.0)
        ->and(app(TotalAllowancesKpi::class)->resolve($company, $range)->value)->toBe(300.0)
        ->and(app(TotalDeductionsKpi::class)->resolve($company, $range)->value)->toBe(75.0)
        ->and(app(OvertimeCostKpi::class)->resolve($company, $range)->value)->toBe(35.0)
        ->and(app(PayrollByDepartmentKpi::class)->resolve($company, $range)->metadata['values'][0]['total_payroll_cost'])->toBe(1500.0)
        ->and(app(LatestPayrollRunStatusKpi::class)->resolve($company, $range)->formattedValue)->toBe(PayrollRunStatus::Approved->label());
});

it('marks payroll KPI definitions with payroll permission protection', function () {
    expect(app(TotalPayrollCostKpi::class)->definition()->requiredPermission)->toBe('payroll_runs.view');
});
