<?php

use App\Enums\EmployeeStatus;
use App\Enums\PayrollRunItemStatus;
use App\Enums\PayrollRunStatus;
use App\Jobs\GeneratePayrollRunJob;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use App\Models\PayrollRunItemComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PayrollAccountingPostingService;
use App\Services\PayrollExportService;
use App\Services\PayrollGenerationQueueService;
use App\Services\PayrollMetrics;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

function grantPayrollBatchPermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function payrollBatchScenario(): array
{
    $company = Company::factory()->create();
    $department = Department::factory()->for($company)->create(['name_ar' => 'المالية']);
    $employee = Employee::factory()->for($company)->create([
        'department_id' => $department->id,
        'employment_status' => EmployeeStatus::Active,
        'employee_number' => 'EMP-076',
        'first_name_ar' => 'محمد',
        'last_name_ar' => 'سالم',
    ]);
    $period = PayrollPeriod::factory()->for($company)->create([
        'name_ar' => 'رواتب يوليو',
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-07-31',
    ]);
    $run = PayrollRun::factory()->for($company)->create([
        'payroll_period_id' => $period->id,
        'status' => PayrollRunStatus::Approved,
        'total_employees' => 1,
        'gross_amount' => 12000,
        'total_allowances' => 2000,
        'total_deductions' => 500,
        'net_amount' => 11500,
        'generated_at' => '2026-07-31 10:00:00',
    ]);
    $item = PayrollRunItem::factory()->for($company)->create([
        'payroll_run_id' => $run->id,
        'employee_id' => $employee->id,
        'basic_salary' => 10000,
        'gross_salary' => 12000,
        'total_allowances' => 2000,
        'total_deductions' => 500,
        'net_salary' => 11500,
        'overtime_amount' => 300,
        'status' => PayrollRunItemStatus::Calculated,
    ]);
    PayrollRunItemComponent::factory()->create([
        'payroll_run_item_id' => $item->id,
        'name_ar' => 'بدل أداء',
        'name_en' => 'Performance',
        'amount' => 2000,
    ]);

    return [$company, $period, $run, $item];
}

it('exports payroll run summaries items and payslip arrays with permissions', function () {
    [$company, $period, $run] = payrollBatchScenario();
    $actor = User::factory()->for($company)->create();
    grantPayrollBatchPermissions($actor, ['payroll_runs.export', 'payslips.view']);
    $this->actingAs($actor);

    $service = app(PayrollExportService::class);
    $summary = $service->runSummary(['payroll_period_id' => $period->id], $actor);
    $items = $service->runItems(['payroll_run_id' => $run->id], $actor);
    $payslips = $service->payslips($run, $actor);

    expect($summary['rows'])->toHaveCount(1)
        ->and($summary['rows'][0]['net_amount'])->toBe('11500.00')
        ->and($items['rows'])->toHaveCount(1)
        ->and($items['rows'][0]['employee_number'])->toBe('EMP-076')
        ->and($payslips['rows'])->toHaveCount(1)
        ->and($payslips['rows'][0]['components'][0]['name_en'])->toBe('Performance');
});

it('blocks payroll exports without export permission', function () {
    [$company] = payrollBatchScenario();
    $actor = User::factory()->for($company)->create();
    $this->actingAs($actor);

    app(PayrollExportService::class)->runSummary([], $actor);
})->throws(AuthorizationException::class);

it('returns payroll metrics for the current company', function () {
    [$company] = payrollBatchScenario();
    $actor = User::factory()->for($company)->create();
    grantPayrollBatchPermissions($actor, ['payroll_runs.view']);
    $this->actingAs($actor);

    $metrics = app(PayrollMetrics::class)->forCurrentCompany([], $actor);

    expect($metrics['total_payroll_cost']['value'])->toBe(11500.0)
        ->and($metrics['average_salary']['value'])->toBe(11500.0)
        ->and($metrics['total_allowances']['value'])->toBe(2000.0)
        ->and($metrics['total_deductions']['value'])->toBe(500.0)
        ->and($metrics['overtime_cost']['value'])->toBe(300.0)
        ->and($metrics['payroll_by_department']['values'][0]['total_payroll_cost'])->toBe(11500.0)
        ->and($metrics['payroll_trend_by_period']['values'][0]['net_amount'])->toBe(11500.0);
});

it('queues payroll generation and records status on the period', function () {
    Bus::fake();
    $company = Company::factory()->create();
    $period = PayrollPeriod::factory()->for($company)->create([
        'starts_on' => '2026-08-01',
        'ends_on' => '2026-08-31',
    ]);
    $actor = User::factory()->for($company)->create();
    grantPayrollBatchPermissions($actor, ['payroll_runs.generate']);
    $this->actingAs($actor);

    $jobKey = app(PayrollGenerationQueueService::class)->dispatch($period, ['run_number' => 'PAY-AUG'], $actor);

    Bus::assertDispatched(GeneratePayrollRunJob::class);
    expect($jobKey)->toBe("payroll-generation:{$company->id}:{$period->id}")
        ->and($period->refresh()->metadata['payroll_generation']['status'])->toBe('queued');
});

it('records accounting placeholder audit then fails safely', function () {
    [$company, , $run] = payrollBatchScenario();
    $actor = User::factory()->for($company)->create();
    grantPayrollBatchPermissions($actor, ['payroll_runs.approve']);
    $this->actingAs($actor);

    try {
        app(PayrollAccountingPostingService::class)->postApprovedPayrollRun($run, $actor);
    } catch (LogicException $exception) {
        expect($exception->getMessage())->toBe('Payroll accounting posting is not available until the accounting module is implemented.')
            ->and(AuditLog::query()->where('action', 'payroll_accounting.posting_requested')->exists())->toBeTrue();

        return;
    }

    $this->fail('Expected accounting placeholder to throw.');
});
