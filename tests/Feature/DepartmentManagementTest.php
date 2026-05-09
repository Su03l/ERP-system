<?php

use App\Enums\DepartmentStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantDepartmentPermissions(User $user, array $permissionKeys): void
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

function departmentPayload(array $overrides = []): array
{
    return array_merge([
        'name_ar' => 'الموارد البشرية',
        'name_en' => 'Human Resources',
        'code' => 'HR',
        'status' => DepartmentStatus::Active->value,
    ], $overrides);
}

test('department index is tenant scoped and filterable', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $parent = Department::factory()->for($company)->create();
    $matchingDepartment = Department::factory()->for($company)->create([
        'parent_id' => $parent->id,
        'name_ar' => 'المالية',
        'status' => DepartmentStatus::Active->value,
    ]);

    Department::factory()->for($company)->create(['status' => DepartmentStatus::Inactive->value]);
    Department::factory()->for($otherCompany)->create(['name_ar' => 'المالية']);
    grantDepartmentPermissions($user, ['departments.view']);

    $this->actingAs($user)
        ->getJson("/departments?search=المالية&parent_id={$parent->id}&status=active")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingDepartment->id);
});

test('department store validates parent inside current company and audits creation', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $otherParent = Department::factory()->for($otherCompany)->create();
    grantDepartmentPermissions($user, ['departments.create']);

    $this->actingAs($user)
        ->postJson('/departments', departmentPayload(['parent_id' => $otherParent->id]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);

    $this->actingAs($user)
        ->postJson('/departments', departmentPayload())
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id)
        ->assertJsonPath('data.code', 'HR');

    expect(AuditLog::query()->where('action', 'department.created')->exists())->toBeTrue();
});

test('department update validates parent and audits changes', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create(['code' => 'OPS']);
    grantDepartmentPermissions($user, ['departments.update']);

    $this->actingAs($user)
        ->patchJson("/departments/{$department->id}", ['parent_id' => $department->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);

    $this->actingAs($user)
        ->patchJson("/departments/{$department->id}", ['name_ar' => 'التشغيل'])
        ->assertSuccessful()
        ->assertJsonPath('data.name_ar', 'التشغيل');

    expect(AuditLog::query()->where('action', 'department.updated')->where('auditable_id', $department->id)->exists())->toBeTrue();
});

test('department routes enforce policy permissions', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();

    $this->actingAs($user)
        ->getJson("/departments/{$department->id}")
        ->assertForbidden();

    grantDepartmentPermissions($user, ['departments.view']);

    $this->actingAs($user)
        ->getJson("/departments/{$department->id}")
        ->assertSuccessful();
});

test('department destroy archives and audits department', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();
    grantDepartmentPermissions($user, ['departments.delete']);

    $this->actingAs($user)
        ->deleteJson("/departments/{$department->id}")
        ->assertNoContent();

    expect($department->refresh()->trashed())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'department.archived')->where('auditable_id', $department->id)->exists())->toBeTrue();
});
