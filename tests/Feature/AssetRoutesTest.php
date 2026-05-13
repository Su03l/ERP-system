<?php

use App\Enums\AssetCategoryStatus;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantAssetRoutePermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('manages asset categories through thin tenant scoped endpoints', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetRoutePermissions($actor, ['asset_categories.view', 'asset_categories.create', 'asset_categories.update', 'asset_categories.delete']);
    AssetCategory::factory()->for(Company::factory())->create(['name_ar' => 'خارجية']);

    $this->actingAs($actor)
        ->postJson('/asset-categories', [
            'name_ar' => 'أجهزة تقنية',
            'code' => 'TECH',
            'status' => AssetCategoryStatus::Active->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id);

    $assetCategory = AssetCategory::query()->where('company_id', $company->id)->firstOrFail();

    $this->actingAs($actor)
        ->getJson('/asset-categories?search=تقنية')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $this->actingAs($actor)
        ->patchJson("/asset-categories/{$assetCategory->id}", ['name_ar' => 'أجهزة محدثة'])
        ->assertSuccessful()
        ->assertJsonPath('data.name_ar', 'أجهزة محدثة');

    $this->actingAs($actor)
        ->deleteJson("/asset-categories/{$assetCategory->id}")
        ->assertNoContent();
});

it('manages assets through thin tenant scoped endpoints and filters index results', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetRoutePermissions($actor, ['assets.view', 'assets.create', 'assets.update', 'assets.delete']);
    $category = AssetCategory::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    Asset::factory()->for(Company::factory())->create(['asset_code' => 'AST-OTHER']);

    $this->actingAs($actor)
        ->postJson('/assets', [
            'asset_category_id' => $category->id,
            'asset_code' => 'AST-101',
            'name_ar' => 'حاسوب محمول',
            'status' => AssetStatus::Available->value,
            'purchase_date' => '2026-05-01',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id);

    $asset = Asset::query()->where('company_id', $company->id)->firstOrFail();

    $this->actingAs($actor)
        ->patchJson("/assets/{$asset->id}", [
            'assigned_employee_id' => $employee->id,
            'status' => AssetStatus::Assigned->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.assigned_employee_id', $employee->id);

    $this->actingAs($actor)
        ->getJson("/assets?category={$category->id}&status=assigned&assigned_employee={$employee->id}&search=AST&purchased_from=2026-05-01&purchased_until=2026-05-31")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.asset_code', 'AST-101');
});

it('does not expose cross-company assets', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    grantAssetRoutePermissions($actor, ['assets.view']);
    $otherAsset = Asset::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->getJson("/assets/{$otherAsset->id}")
        ->assertForbidden();
});
