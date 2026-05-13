<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

function grantAssetPolicyPermissions(User $user, array $permissions): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissions as $permissionKey) {
        $permission = Permission::factory()->create(['key' => $permissionKey]);
        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

it('protects asset operations with permission keys and company boundary', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAssetPolicyPermissions($user, ['assets.view', 'assets.create', 'assets.update', 'assets.delete', 'assets.export']);
    $asset = Asset::factory()->for($company)->create();
    $otherAsset = Asset::factory()->for(Company::factory())->create();

    expect(Gate::forUser($user)->allows('viewAny', Asset::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', Asset::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $asset))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $asset))->toBeTrue()
        ->and(Gate::forUser($user)->allows('delete', $asset))->toBeTrue()
        ->and(Gate::forUser($user)->allows('export', Asset::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $otherAsset))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $otherAsset))->toBeTrue();
});

it('protects asset category operations with permission keys and company boundary', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    grantAssetPolicyPermissions($user, ['asset_categories.view', 'asset_categories.create', 'asset_categories.update', 'asset_categories.delete']);
    $assetCategory = AssetCategory::factory()->for($company)->create();
    $otherAssetCategory = AssetCategory::factory()->for(Company::factory())->create();

    expect(Gate::forUser($user)->allows('viewAny', AssetCategory::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', AssetCategory::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $assetCategory))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $assetCategory))->toBeTrue()
        ->and(Gate::forUser($user)->allows('delete', $assetCategory))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $otherAssetCategory))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $otherAssetCategory))->toBeTrue();
});

it('protects asset custody gates with permission keys', function (string $ability) {
    $user = User::factory()->for(Company::factory())->create();
    grantAssetPolicyPermissions($user, [$ability]);

    expect(Gate::forUser($user)->allows($ability))->toBeTrue();
})->with([
    'asset_custody.view',
    'asset_custody.create',
    'asset_custody.approve',
    'asset_custody.return',
]);
