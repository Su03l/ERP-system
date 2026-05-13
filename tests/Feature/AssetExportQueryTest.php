<?php

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AssetExportQuery;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantAssetExportPermission(User $user): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::factory()->create(['key' => 'assets.export']);
    $role->permissions()->attach($permission);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('returns tenant scoped export-ready asset rows with filters', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetExportPermission($actor);
    $category = AssetCategory::factory()->for($company)->create(['name_ar' => 'تقنية']);
    $employee = Employee::factory()->for($company)->create(['employee_number' => 'EMP-1', 'first_name_ar' => 'أحمد', 'last_name_ar' => 'علي']);
    Asset::factory()->for($company)->create([
        'asset_category_id' => $category->id,
        'assigned_employee_id' => $employee->id,
        'asset_code' => 'AST-001',
        'name_ar' => 'حاسوب',
        'status' => AssetStatus::Assigned,
    ]);
    Asset::factory()->for(Company::factory())->create(['asset_code' => 'AST-001']);

    $rows = app(AssetExportQuery::class)->rows([
        'category' => $category->id,
        'status' => AssetStatus::Assigned->value,
        'assigned_employee' => $employee->id,
        'search' => 'AST',
    ], $actor);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['asset_code'])->toBe('AST-001')
        ->and($rows[0]['category_ar'])->toBe('تقنية')
        ->and($rows[0]['assigned_employee_number'])->toBe('EMP-1');
});

it('requires export permission', function () {
    $actor = User::factory()->for(Company::factory())->create();

    app(AssetExportQuery::class)->rows([], $actor);
})->throws(AuthorizationException::class);
