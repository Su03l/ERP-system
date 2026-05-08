<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function grantEmployeePolicyPermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('employee policy allows operations by permission key', function (string $ability, string $permissionKey) {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    grantEmployeePolicyPermissions($user, [$permissionKey]);
    $this->actingAs($user);

    expect(Gate::forUser($user)->allows($ability, $employee))->toBeTrue();
})->with([
    'view' => ['view', 'employees.view'],
    'update' => ['update', 'employees.update'],
    'delete' => ['delete', 'employees.delete'],
    'view salary' => ['viewSalary', 'employees.view_salary'],
    'update salary' => ['updateSalary', 'employees.update_salary'],
]);

test('employee policy allows view any and create by permission key', function (string $ability, string $permissionKey) {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    grantEmployeePolicyPermissions($user, [$permissionKey]);
    $this->actingAs($user);

    expect(Gate::forUser($user)->allows($ability, Employee::class))->toBeTrue();
})->with([
    'view any' => ['viewAny', 'employees.view'],
    'create' => ['create', 'employees.create'],
]);

test('employee policy denies cross company employee access', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($otherCompany)->create();

    grantEmployeePolicyPermissions($user, [
        'employees.view',
        'employees.update',
        'employees.delete',
        'employees.view_salary',
        'employees.update_salary',
    ]);
    $this->actingAs($user);

    expect(Gate::forUser($user)->denies('view', $employee))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $employee))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $employee))->toBeTrue()
        ->and(Gate::forUser($user)->denies('viewSalary', $employee))->toBeTrue()
        ->and(Gate::forUser($user)->denies('updateSalary', $employee))->toBeTrue();
});

test('employee policy protects salary with dedicated permissions', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    grantEmployeePolicyPermissions($user, ['employees.view', 'employees.update']);
    $this->actingAs($user);

    expect(Gate::forUser($user)->allows('view', $employee))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $employee))->toBeTrue()
        ->and(Gate::forUser($user)->denies('viewSalary', $employee))->toBeTrue()
        ->and(Gate::forUser($user)->denies('updateSalary', $employee))->toBeTrue();
});
