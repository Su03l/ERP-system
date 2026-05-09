<?php

use App\Enums\EmployeeStatus;
use App\Enums\WorkType;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\EmployeeExportQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantEmployeeExportPermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            [
                'name' => $permissionKey,
                'description' => null,
            ],
        );

        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('employee export is scoped to current company and supports filters', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create(['name_ar' => 'الموارد البشرية']);
    $jobTitle = JobTitle::factory()->for($company)->create(['name_ar' => 'محاسب']);
    $employee = Employee::factory()->for($company)->create([
        'department_id' => $department->id,
        'job_title_id' => $jobTitle->id,
        'employee_number' => 'EMP-700',
        'first_name_ar' => 'سارة',
        'last_name_ar' => 'أحمد',
        'first_name_en' => 'Sara',
        'last_name_en' => 'Ahmed',
        'employment_status' => EmployeeStatus::Active->value,
        'work_type' => WorkType::FullTime->value,
        'hire_date' => '2026-05-01',
        'basic_salary' => 15000,
    ]);
    Employee::factory()->for($company)->create(['employee_number' => 'EMP-701', 'employment_status' => EmployeeStatus::Inactive->value]);
    Employee::factory()->for($otherCompany)->create(['employee_number' => 'EMP-700']);

    $this->actingAs($user);

    $export = app(EmployeeExportQuery::class)->export([
        'search' => 'سارة',
        'department_id' => $department->id,
        'job_title_id' => $jobTitle->id,
        'status' => EmployeeStatus::Active->value,
    ], $user);

    expect($export['entity_type'])->toBe('employees')
        ->and($export['module_key'])->toBe('hr')
        ->and($export['rows'])->toHaveCount(1)
        ->and($export['rows'][0]['employee_number'])->toBe($employee->employee_number)
        ->and($export['rows'][0]['name_ar'])->toBe('سارة أحمد')
        ->and($export['rows'][0]['name_en'])->toBe('Sara Ahmed')
        ->and($export['rows'][0]['department'])->toBe('الموارد البشرية')
        ->and($export['rows'][0])->not->toHaveKey('basic_salary');
});

test('employee export includes salary only with salary permission', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $salaryUser = User::factory()->for($company)->create();
    Employee::factory()->for($company)->create(['employee_number' => 'EMP-702', 'basic_salary' => 12345.67]);

    grantEmployeeExportPermissions($salaryUser, ['employees.view_salary']);

    $this->actingAs($user);
    $export = app(EmployeeExportQuery::class)->export(actor: $user);

    $this->actingAs($salaryUser);
    $salaryExport = app(EmployeeExportQuery::class)->export(actor: $salaryUser);

    expect($export['includes_salary'])->toBeFalse()
        ->and($export['rows'][0])->not->toHaveKey('basic_salary')
        ->and($salaryExport['includes_salary'])->toBeTrue()
        ->and($salaryExport['rows'][0]['basic_salary'])->toBe('12345.67')
        ->and(collect($salaryExport['columns'])->pluck('key')->all())->toContain('basic_salary');
});

test('employee export returns empty rows without current company context', function () {
    $company = Company::factory()->create();
    Employee::factory()->for($company)->create(['employee_number' => 'EMP-703']);

    $export = app(EmployeeExportQuery::class)->export();

    expect($export['rows'])->toBe([])
        ->and($export['includes_salary'])->toBeFalse();
});
