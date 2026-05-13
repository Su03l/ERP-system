<?php

use App\Actions\ArchiveAsset;
use App\Actions\CreateAsset;
use App\Actions\CreateAssetCategory;
use App\Actions\UpdateAsset;
use App\Actions\UpdateAssetCategory;
use App\Enums\AssetCategoryStatus;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantAssetPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('creates asset categories with tenant ownership and audit logging', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetPermissions($actor, ['asset_categories.create']);
    $this->actingAs($actor);

    $assetCategory = app(CreateAssetCategory::class)->handle([
        'company_id' => Company::factory()->create()->id,
        'name_ar' => 'أجهزة تقنية',
        'code' => 'TECH',
        'status' => AssetCategoryStatus::Active,
    ]);

    expect($assetCategory->company_id)->toBe($company->id)
        ->and($assetCategory->name_ar)->toBe('أجهزة تقنية')
        ->and(AuditLog::query()->where('action', 'asset_category.created')->where('auditable_id', $assetCategory->id)->exists())->toBeTrue();
});

it('updates asset categories inside the current company only', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetPermissions($actor, ['asset_categories.update']);
    $assetCategory = AssetCategory::factory()->for($company)->create();
    $this->actingAs($actor);

    app(UpdateAssetCategory::class)->handle($assetCategory, [
        'name_ar' => 'تصنيف محدث',
        'status' => AssetCategoryStatus::Inactive,
    ]);

    expect($assetCategory->refresh()->name_ar)->toBe('تصنيف محدث')
        ->and($assetCategory->status)->toBe(AssetCategoryStatus::Inactive)
        ->and(AuditLog::query()->where('action', 'asset_category.updated')->where('auditable_id', $assetCategory->id)->exists())->toBeTrue();
});

it('rejects self parent asset category updates', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetPermissions($actor, ['asset_categories.update']);
    $assetCategory = AssetCategory::factory()->for($company)->create();
    $this->actingAs($actor);

    app(UpdateAssetCategory::class)->handle($assetCategory, [
        'parent_id' => $assetCategory->id,
    ]);
})->throws(ValidationException::class);

it('creates and updates assets with tenant ownership and audit logging', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetPermissions($actor, ['assets.create', 'assets.update']);
    $category = AssetCategory::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $this->actingAs($actor);

    $asset = app(CreateAsset::class)->handle([
        'company_id' => Company::factory()->create()->id,
        'asset_category_id' => $category->id,
        'asset_code' => 'AST-100',
        'name_ar' => 'حاسوب محمول',
        'status' => AssetStatus::Available,
    ]);

    app(UpdateAsset::class)->handle($asset, [
        'assigned_employee_id' => $employee->id,
        'status' => AssetStatus::Assigned,
        'current_value' => '3500.00',
    ]);

    expect($asset->refresh()->company_id)->toBe($company->id)
        ->and($asset->assigned_employee_id)->toBe($employee->id)
        ->and($asset->current_value)->toBe('3500.00')
        ->and(AuditLog::query()->where('action', 'asset.created')->where('auditable_id', $asset->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'asset.updated')->where('auditable_id', $asset->id)->exists())->toBeTrue();
});

it('archives unassigned assets with a soft delete and audit logging', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetPermissions($actor, ['assets.delete']);
    $asset = Asset::factory()->for($company)->create([
        'assigned_employee_id' => null,
        'status' => AssetStatus::Available,
    ]);
    $this->actingAs($actor);

    app(ArchiveAsset::class)->handle($asset);

    expect($asset->refresh()->trashed())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'asset.archived')->where('auditable_id', $asset->id)->exists())->toBeTrue();
});

it('prevents archiving assigned assets', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetPermissions($actor, ['assets.delete']);
    $employee = Employee::factory()->for($company)->create();
    $asset = Asset::factory()->for($company)->create([
        'assigned_employee_id' => $employee->id,
        'status' => AssetStatus::Assigned,
    ]);
    $this->actingAs($actor);

    app(ArchiveAsset::class)->handle($asset);
})->throws(ValidationException::class);

it('requires asset permissions for actions', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $this->actingAs($actor);

    app(CreateAsset::class)->handle([
        'asset_code' => 'AST-200',
        'name_ar' => 'أصل',
        'status' => AssetStatus::Available,
    ]);
})->throws(AuthorizationException::class);
