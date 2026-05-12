<?php

use App\Enums\EmployeeStatus;
use App\Enums\PayrollPeriodStatus;
use App\Enums\PayrollRunItemStatus;
use App\Enums\PayrollRunStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollRun;
use App\Models\PayrollRunItem;
use App\Models\PayrollRunItemComponent;
use App\Models\PayrollSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantPayrollRoutePermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(['key' => $permissionKey], ['name' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('exposes payroll settings components periods and runs through authorized routes', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    PayrollSetting::factory()->for($company)->create();
    SalaryComponent::factory()->for($company)->create(['name_ar' => 'بدل اختبار', 'code' => 'TEST']);
    $period = PayrollPeriod::factory()->for($company)->create([
        'starts_on' => '2026-05-01',
        'ends_on' => '2026-05-31',
        'status' => PayrollPeriodStatus::Open,
    ]);
    PayrollRun::factory()->for($company)->create([
        'payroll_period_id' => $period->id,
        'status' => PayrollRunStatus::PendingApproval,
    ]);
    grantPayrollRoutePermissions($user, [
        'payroll_settings.view',
        'salary_components.view',
        'payroll_periods.view',
        'payroll_runs.view',
    ]);

    $this->actingAs($user)
        ->getJson('/payroll-settings')
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id);

    $this->actingAs($user)
        ->getJson('/salary-components?search=TEST')
        ->assertSuccessful()
        ->assertJsonPath('data.0.code', 'TEST');

    $this->actingAs($user)
        ->getJson('/payroll-periods?status=open')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $period->id);

    $this->actingAs($user)
        ->getJson('/payroll-runs?status=pending_approval')
        ->assertSuccessful()
        ->assertJsonPath('data.0.payroll_period_id', $period->id);
});

it('generates payslip data from payroll run item and allows employee to view own payslip', function () {
    $company = Company::factory()->create(['name' => 'Nawwat Test']);
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create([
        'user_id' => $user->id,
        'employment_status' => EmployeeStatus::Active,
        'employee_number' => 'EMP-100',
        'first_name_ar' => 'سارة',
        'last_name_ar' => 'أحمد',
    ]);
    $period = PayrollPeriod::factory()->for($company)->create([
        'starts_on' => '2026-06-01',
        'ends_on' => '2026-06-30',
    ]);
    $run = PayrollRun::factory()->for($company)->create([
        'payroll_period_id' => $period->id,
    ]);
    $item = PayrollRunItem::factory()->for($company)->create([
        'payroll_run_id' => $run->id,
        'employee_id' => $employee->id,
        'basic_salary' => 10000,
        'gross_salary' => 12000,
        'total_allowances' => 2000,
        'total_deductions' => 500,
        'net_salary' => 11500,
        'status' => PayrollRunItemStatus::Calculated,
    ]);
    PayrollRunItemComponent::factory()->create([
        'payroll_run_item_id' => $item->id,
        'name_ar' => 'بدل سكن',
        'name_en' => 'Housing',
        'amount' => 2000,
    ]);

    $this->actingAs($user)
        ->getJson("/payroll-run-items/{$item->id}/payslip")
        ->assertSuccessful()
        ->assertJsonPath('data.company.name', 'Nawwat Test')
        ->assertJsonPath('data.employee.employee_number', 'EMP-100')
        ->assertJsonPath('data.earnings.basic_salary', '10000.00')
        ->assertJsonPath('data.net_salary', '11500.00')
        ->assertJsonPath('data.components.0.name_en', 'Housing');
});
