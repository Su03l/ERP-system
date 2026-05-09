<?php

use App\Enums\EmployeeStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantEmployeeRoutePermissions(User $user, array $permissionKeys): void
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

function employeeRoutePayload(array $overrides = []): array
{
    return array_merge([
        'employee_number' => 'EMP-900',
        'first_name_ar' => 'نورة',
        'last_name_ar' => 'خالد',
        'employment_status' => EmployeeStatus::Active->value,
    ], $overrides);
}

test('employee index is tenant scoped and supports basic filters', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();
    $jobTitle = JobTitle::factory()->for($company)->create();
    $matchingEmployee = Employee::factory()->for($company)->create([
        'department_id' => $department->id,
        'job_title_id' => $jobTitle->id,
        'first_name_ar' => 'راشد',
        'employment_status' => EmployeeStatus::Active->value,
    ]);

    Employee::factory()->for($company)->create([
        'department_id' => null,
        'job_title_id' => null,
        'first_name_ar' => 'مختلف',
        'employment_status' => EmployeeStatus::Inactive->value,
    ]);
    Employee::factory()->for($otherCompany)->create(['first_name_ar' => 'راشد']);
    grantEmployeeRoutePermissions($user, ['employees.view']);

    $this->actingAs($user)
        ->getJson("/employees?search=راشد&department_id={$department->id}&job_title_id={$jobTitle->id}&status=active")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingEmployee->id);
});

test('employee store endpoint uses form request action and audit logging', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantEmployeeRoutePermissions($user, ['employees.create']);

    $this->actingAs($user)
        ->postJson('/employees', employeeRoutePayload())
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id)
        ->assertJsonPath('data.employee_number', 'EMP-900');

    expect(Employee::query()->where('company_id', $company->id)->where('employee_number', 'EMP-900')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'employee.created')->exists())->toBeTrue();
});

test('employee show hides salary unless user has salary permission', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $salaryUser = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create(['basic_salary' => 12345.67]);

    grantEmployeeRoutePermissions($user, ['employees.view']);
    grantEmployeeRoutePermissions($salaryUser, ['employees.view', 'employees.view_salary']);

    $this->actingAs($user)
        ->getJson("/employees/{$employee->id}")
        ->assertSuccessful()
        ->assertJsonMissingPath('data.basic_salary');

    $this->actingAs($salaryUser)
        ->getJson("/employees/{$employee->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.basic_salary', '12345.67');
});

test('employee update endpoint uses form request action and policy authorization', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create(['first_name_ar' => 'قديم']);
    grantEmployeeRoutePermissions($user, ['employees.update']);

    $this->actingAs($user)
        ->patchJson("/employees/{$employee->id}", ['first_name_ar' => 'جديد'])
        ->assertSuccessful()
        ->assertJsonPath('data.first_name_ar', 'جديد');

    expect(AuditLog::query()->where('action', 'employee.updated')->where('auditable_id', $employee->id)->exists())->toBeTrue();
});

test('employee destroy endpoint archives employee', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantEmployeeRoutePermissions($user, ['employees.delete']);

    $this->actingAs($user)
        ->deleteJson("/employees/{$employee->id}")
        ->assertNoContent();

    expect($employee->refresh()->trashed())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'employee.archived')->where('auditable_id', $employee->id)->exists())->toBeTrue();
});
