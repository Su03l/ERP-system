<?php

use App\Actions\ApprovePayrollRun;
use App\Actions\CreateSalaryPackage;
use App\Actions\GeneratePayrollRun;
use App\Enums\AttendanceStatus;
use App\Enums\EmployeeStatus;
use App\Enums\PayrollRunStatus;
use App\Enums\SalaryPackageStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\PayrollSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\PayrollCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantPayrollCoveragePermissions213(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey, 'description' => null],
        );

        $role->permissions()->syncWithoutDetaching([$permission->id]);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function payrollCoverageScenario213(): array
{
    $company = Company::factory()->create([
        'settings' => [
            'attendance' => [
                'work_start_time' => '09:00',
                'work_end_time' => '17:00',
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            ],
            'payroll' => [
                'overtime_multiplier' => 1.5,
            ],
        ],
    ]);
    PayrollSetting::factory()->for($company)->create([
        'overtime_calculation_enabled' => true,
        'absence_deduction_enabled' => true,
        'late_deduction_enabled' => true,
        'payroll_approval_required' => true,
    ]);
    $period = PayrollPeriod::factory()->for($company)->create([
        'starts_on' => '2026-04-06',
        'ends_on' => '2026-04-10',
    ]);
    $employee = Employee::factory()->for($company)->create([
        'employment_status' => EmployeeStatus::Active,
    ]);
    EmployeeSalaryPackage::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'basic_salary' => 10000,
        'housing_allowance' => 1000,
        'transportation_allowance' => 500,
        'effective_from' => '2026-01-01',
        'effective_to' => null,
        'status' => SalaryPackageStatus::Active,
    ]);
    AttendanceRecord::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'attendance_date' => '2026-04-06',
        'status' => AttendanceStatus::Late,
        'late_minutes' => 60,
        'overtime_minutes' => 120,
    ]);

    return [$company, $period, $employee];
}

test('salary packages can be created but conflicting active packages are rejected', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantPayrollCoveragePermissions213($actor, ['salary_packages.create']);

    $this->actingAs($actor);

    $salaryPackage = app(CreateSalaryPackage::class)->handle([
        'employee_id' => $employee->id,
        'basic_salary' => 12000,
        'effective_from' => '2026-01-01',
        'status' => SalaryPackageStatus::Active->value,
    ], $actor);

    expect($salaryPackage->company_id)->toBe($company->id)
        ->and($salaryPackage->basic_salary)->toBe('12000.00');

    app(CreateSalaryPackage::class)->handle([
        'employee_id' => $employee->id,
        'basic_salary' => 13000,
        'effective_from' => '2026-02-01',
        'status' => SalaryPackageStatus::Active->value,
    ], $actor);
})->throws(ValidationException::class);

test('payroll calculation and run generation are exact and duplicate safe', function () {
    [$company, $period, $employee] = payrollCoverageScenario213();
    $actor = User::factory()->for($company)->create();
    grantPayrollCoveragePermissions213($actor, ['payroll_runs.generate']);

    $calculation = app(PayrollCalculationService::class)->calculate($employee, $period);

    expect($calculation['basic_salary'])->toBe('10000.00')
        ->and($calculation['total_allowances'])->toBe('1500.00')
        ->and($calculation['attendance_deduction'])->toBe('249.60')
        ->and($calculation['overtime_amount'])->toBe('748.80')
        ->and($calculation['gross_salary'])->toBe('12248.80')
        ->and($calculation['net_salary'])->toBe('11999.20')
        ->and(PayrollRun::query()->count())->toBe(0);

    $this->actingAs($actor);

    $run = app(GeneratePayrollRun::class)->handle($period, ['run_number' => 'PAY-213-APR'], $actor);

    expect($run->total_employees)->toBe(1)
        ->and($run->gross_amount)->toBe('12248.80')
        ->and($run->net_amount)->toBe('11999.20')
        ->and($run->items)->toHaveCount(1);

    app(GeneratePayrollRun::class)->handle($period, ['run_number' => 'PAY-213-DUP'], $actor);
})->throws(ValidationException::class);

test('payroll approval workflow completes and payslips stay protected', function () {
    [$company, $period, $employee] = payrollCoverageScenario213();
    $generator = User::factory()->for($company)->create();
    $approver = User::factory()->for($company)->create();
    $otherUser = User::factory()->for($company)->create();
    grantPayrollCoveragePermissions213($generator, ['payroll_runs.generate']);
    grantPayrollCoveragePermissions213($approver, ['payroll_runs.approve']);

    $workflow = Workflow::factory()->for($company)->create([
        'module_key' => 'payroll',
        'trigger_type' => 'payroll_run_approval',
        'status' => 'active',
    ]);
    WorkflowStep::factory()->for($workflow)->create([
        'approver_type' => 'permission',
        'approver_value' => 'payroll_runs.approve',
        'order' => 1,
    ]);

    $this->actingAs($generator);

    $run = app(GeneratePayrollRun::class)->handle($period, ['run_number' => 'PAY-213-WF'], $generator);

    expect($run->status)->toBe(PayrollRunStatus::PendingApproval)
        ->and($run->workflow_instance_id)->not->toBeNull();

    $this->actingAs($approver);
    $approved = app(ApprovePayrollRun::class)->handle($run->refresh(), $approver, 'Approved.');

    expect($approved->status)->toBe(PayrollRunStatus::Approved)
        ->and($approved->approved_by)->toBe($approver->id);

    $item = $approved->items()->firstOrFail();

    $this->actingAs($otherUser)
        ->getJson("/payroll-run-items/{$item->id}/payslip")
        ->assertForbidden();

    $employee->forceFill(['user_id' => $otherUser->id])->save();

    $this->actingAs($otherUser->refresh())
        ->getJson("/payroll-run-items/{$item->id}/payslip")
        ->assertSuccessful()
        ->assertJsonPath('data.net_salary', '11999.20');
});
