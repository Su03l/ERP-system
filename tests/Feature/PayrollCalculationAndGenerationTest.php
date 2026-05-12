<?php

use App\Actions\GeneratePayrollRun;
use App\Enums\AttendanceStatus;
use App\Enums\EmployeeStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\PayrollRunStatus;
use App\Enums\SalaryCalculationType;
use App\Enums\SalaryComponentType;
use App\Enums\SalaryPackageStatus;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\PayrollSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\User;
use App\Services\PayrollCalculationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantPayrollRunPermission(User $user): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::factory()->create(['key' => 'payroll_runs.generate']);

    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function payrollScenario(): array
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
    ]);
    $period = PayrollPeriod::factory()->for($company)->create([
        'starts_on' => '2026-01-05',
        'ends_on' => '2026-01-09',
    ]);
    $employee = Employee::factory()->for($company)->create([
        'employment_status' => EmployeeStatus::Active,
    ]);
    $salaryPackage = EmployeeSalaryPackage::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'basic_salary' => 10000,
        'housing_allowance' => 1000,
        'transportation_allowance' => 500,
        'effective_from' => '2026-01-01',
        'effective_to' => null,
        'status' => SalaryPackageStatus::Active,
    ]);
    $allowance = SalaryComponent::factory()->for($company)->create([
        'type' => SalaryComponentType::Allowance,
        'calculation_type' => SalaryCalculationType::Percentage,
        'name_ar' => 'بدل أداء',
        'name_en' => 'Performance allowance',
    ]);
    $deduction = SalaryComponent::factory()->for($company)->create([
        'type' => SalaryComponentType::Deduction,
        'calculation_type' => SalaryCalculationType::Fixed,
        'name_ar' => 'استقطاع تأمين',
        'name_en' => 'Insurance deduction',
    ]);

    $salaryPackage->items()->create([
        'company_id' => $company->id,
        'salary_component_id' => $allowance->id,
        'amount' => null,
        'percentage' => 10,
    ]);
    $salaryPackage->items()->create([
        'company_id' => $company->id,
        'salary_component_id' => $deduction->id,
        'amount' => 300,
        'percentage' => null,
    ]);

    AttendanceRecord::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'attendance_date' => '2026-01-05',
        'status' => AttendanceStatus::Late,
        'late_minutes' => 60,
        'overtime_minutes' => 120,
    ]);
    AttendanceRecord::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'attendance_date' => '2026-01-06',
        'status' => AttendanceStatus::Absent,
        'late_minutes' => 0,
        'overtime_minutes' => 0,
    ]);

    $leaveType = LeaveType::factory()->for($company)->create(['is_paid' => false]);
    LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-01-07',
        'end_date' => '2026-01-07',
        'total_days' => 1,
        'status' => LeaveRequestStatus::Approved,
    ]);

    return [$company, $period, $employee];
}

it('calculates payroll for one employee without writing run records', function () {
    [, $period, $employee] = payrollScenario();

    $result = app(PayrollCalculationService::class)->calculate($employee, $period);

    expect($result['basic_salary'])->toBe('10000.00')
        ->and($result['total_allowances'])->toBe('2500.00')
        ->and($result['attendance_deduction'])->toBe('2249.60')
        ->and($result['leave_deduction'])->toBe('2000.00')
        ->and($result['overtime_amount'])->toBe('748.80')
        ->and($result['gross_salary'])->toBe('13248.80')
        ->and($result['total_deductions'])->toBe('4549.60')
        ->and($result['net_salary'])->toBe('8699.20')
        ->and($result['components'])->toHaveCount(4)
        ->and(PayrollRun::query()->count())->toBe(0);
});

it('generates payroll run items totals components and audit log', function () {
    [$company, $period, $employee] = payrollScenario();
    $actor = User::factory()->for($company)->create();
    grantPayrollRunPermission($actor);

    $this->actingAs($actor);

    $run = app(GeneratePayrollRun::class)->handle($period, [
        'run_number' => 'PAY-JAN-2026',
    ], $actor);

    expect($run->company_id)->toBe($company->id)
        ->and($run->status)->toBe(PayrollRunStatus::PendingApproval)
        ->and($run->total_employees)->toBe(1)
        ->and($run->gross_amount)->toBe('13248.80')
        ->and($run->total_allowances)->toBe('2500.00')
        ->and($run->total_deductions)->toBe('4549.60')
        ->and($run->net_amount)->toBe('8699.20')
        ->and($run->items)->toHaveCount(1)
        ->and($run->items->first()->employee_id)->toBe($employee->id)
        ->and($run->items->first()->components)->toHaveCount(4)
        ->and(AuditLog::query()->where('action', 'payroll_run.generated')->where('auditable_id', $run->id)->exists())->toBeTrue();
});

it('prevents duplicate payroll runs unless explicitly allowed', function () {
    [$company, $period] = payrollScenario();
    $actor = User::factory()->for($company)->create();
    grantPayrollRunPermission($actor);

    $this->actingAs($actor);

    app(GeneratePayrollRun::class)->handle($period, ['run_number' => 'PAY-ONE'], $actor);

    app(GeneratePayrollRun::class)->handle($period, ['run_number' => 'PAY-TWO'], $actor);
})->throws(ValidationException::class);

it('requires payroll run permission to generate runs', function () {
    [$company, $period] = payrollScenario();
    $actor = User::factory()->for($company)->create();

    $this->actingAs($actor);

    app(GeneratePayrollRun::class)->handle($period, [], $actor);
})->throws(AuthorizationException::class);
