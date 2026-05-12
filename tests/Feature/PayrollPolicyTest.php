<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSalaryPackage;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use App\Models\PayrollSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantPayrollPolicyPermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey],
        );

        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('payroll policies protect payroll settings components packages periods runs and payslips', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $payrollSetting = PayrollSetting::factory()->for($company)->create();
    $otherPayrollSetting = PayrollSetting::factory()->for($otherCompany)->create();
    $salaryComponent = SalaryComponent::factory()->for($company)->create();
    $otherSalaryComponent = SalaryComponent::factory()->for($otherCompany)->create();
    $salaryPackage = EmployeeSalaryPackage::factory()->for($company)->create();
    $otherSalaryPackage = EmployeeSalaryPackage::factory()->for($otherCompany)->create();
    $payrollPeriod = PayrollPeriod::factory()->for($company)->create([
        'starts_on' => '2026-04-01',
        'ends_on' => '2026-04-30',
    ]);
    $otherPayrollPeriod = PayrollPeriod::factory()->for($otherCompany)->create([
        'starts_on' => '2026-04-01',
        'ends_on' => '2026-04-30',
    ]);
    $payrollRun = PayrollRun::factory()->for($company)->create([
        'payroll_period_id' => $payrollPeriod->id,
    ]);
    $otherPayrollRun = PayrollRun::factory()->for($otherCompany)->create([
        'payroll_period_id' => $otherPayrollPeriod->id,
    ]);
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $payrollRunItem = PayrollRunItem::factory()->for($company)->create([
        'payroll_run_id' => $payrollRun->id,
        'employee_id' => $employee->id,
    ]);
    $otherPayrollRunItem = PayrollRunItem::factory()->for($otherCompany)->create([
        'payroll_run_id' => $otherPayrollRun->id,
        'employee_id' => $otherEmployee->id,
    ]);

    expect($user->can('view', $payrollRun))->toBeFalse()
        ->and($user->can('generate', PayrollRun::class))->toBeFalse()
        ->and($user->can('view', $payrollRunItem))->toBeFalse();

    grantPayrollPolicyPermissions($user, [
        'payroll_settings.view',
        'payroll_settings.update',
        'salary_components.view',
        'salary_components.create',
        'salary_components.update',
        'salary_packages.view',
        'salary_packages.create',
        'salary_packages.update',
        'payroll_periods.view',
        'payroll_periods.create',
        'payroll_periods.update',
        'payroll_runs.view',
        'payroll_runs.generate',
        'payroll_runs.approve',
        'payroll_runs.reject',
        'payroll_runs.export',
        'payslips.view',
    ]);

    expect($user->can('view', $payrollSetting))->toBeTrue()
        ->and($user->can('view', $otherPayrollSetting))->toBeFalse()
        ->and($user->can('update', $payrollSetting))->toBeTrue()
        ->and($user->can('view', $salaryComponent))->toBeTrue()
        ->and($user->can('view', $otherSalaryComponent))->toBeFalse()
        ->and($user->can('create', SalaryComponent::class))->toBeTrue()
        ->and($user->can('update', $salaryComponent))->toBeTrue()
        ->and($user->can('view', $salaryPackage))->toBeTrue()
        ->and($user->can('view', $otherSalaryPackage))->toBeFalse()
        ->and($user->can('create', EmployeeSalaryPackage::class))->toBeTrue()
        ->and($user->can('update', $salaryPackage))->toBeTrue()
        ->and($user->can('view', $payrollPeriod))->toBeTrue()
        ->and($user->can('view', $otherPayrollPeriod))->toBeFalse()
        ->and($user->can('create', PayrollPeriod::class))->toBeTrue()
        ->and($user->can('update', $payrollPeriod))->toBeTrue()
        ->and($user->can('view', $payrollRun))->toBeTrue()
        ->and($user->can('view', $otherPayrollRun))->toBeFalse()
        ->and($user->can('generate', PayrollRun::class))->toBeTrue()
        ->and($user->can('approve', $payrollRun))->toBeTrue()
        ->and($user->can('approve', $otherPayrollRun))->toBeFalse()
        ->and($user->can('reject', $payrollRun))->toBeTrue()
        ->and($user->can('export', PayrollRun::class))->toBeTrue()
        ->and($user->can('view', $payrollRunItem))->toBeTrue()
        ->and($user->can('view', $otherPayrollRunItem))->toBeFalse();
});
