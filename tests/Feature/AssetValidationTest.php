<?php

use App\Enums\AssetCategoryStatus;
use App\Enums\AssetDepreciationMethod;
use App\Enums\AssetStatus;
use App\Http\Requests\StoreAssetCategoryRequest;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetCategoryRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::post('/test/assets/categories', fn (StoreAssetCategoryRequest $request) => $request->validated());
    Route::patch('/test/assets/categories/{assetCategory}', fn (UpdateAssetCategoryRequest $request, AssetCategory $assetCategory) => $request->validated());
    Route::post('/test/assets', fn (StoreAssetRequest $request) => $request->validated());
    Route::patch('/test/assets/{asset}', fn (UpdateAssetRequest $request, Asset $asset) => $request->validated());
});

it('validates asset category payloads inside the current company', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    AssetCategory::factory()->for($company)->create(['code' => 'TECH']);
    $otherParent = AssetCategory::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->postJson('/test/assets/categories', [
            'parent_id' => $otherParent->id,
            'code' => 'TECH',
            'status' => 'missing',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id', 'code', 'name_ar', 'status']);

    $this->actingAs($actor)
        ->postJson('/test/assets/categories', [
            'name_ar' => 'أجهزة تقنية',
            'code' => 'DEVICES',
            'status' => AssetCategoryStatus::Active->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('name_ar', 'أجهزة تقنية');
});

it('validates asset category update payloads without allowing self parent', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $assetCategory = AssetCategory::factory()->for($company)->create(['code' => 'TECH']);

    $this->actingAs($actor)
        ->patchJson("/test/assets/categories/{$assetCategory->id}", [
            'parent_id' => $assetCategory->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['parent_id']);

    $this->actingAs($actor)
        ->patchJson("/test/assets/categories/{$assetCategory->id}", [
            'code' => 'TECH',
            'name_ar' => 'أجهزة محدثة',
        ])
        ->assertSuccessful()
        ->assertJsonPath('name_ar', 'أجهزة محدثة');
});

it('validates asset payloads with tenant scoped relations and decimal fields', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    Asset::factory()->for($company)->create(['asset_code' => 'AST-001']);
    $otherCategory = AssetCategory::factory()->for(Company::factory())->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->postJson('/test/assets', [
            'asset_category_id' => $otherCategory->id,
            'asset_code' => 'AST-001',
            'assigned_employee_id' => $otherEmployee->id,
            'purchase_cost' => -10,
            'current_value' => 'not-money',
            'status' => 'missing',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'asset_category_id',
            'asset_code',
            'assigned_employee_id',
            'name_ar',
            'purchase_cost',
            'current_value',
            'status',
        ]);
});

it('allows valid asset payloads for the current company', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $category = AssetCategory::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    $this->actingAs($actor)
        ->postJson('/test/assets', [
            'asset_category_id' => $category->id,
            'asset_code' => 'AST-002',
            'name_ar' => 'حاسوب محمول',
            'assigned_employee_id' => $employee->id,
            'purchase_cost' => '5000.00',
            'current_value' => '4000.00',
            'status' => AssetStatus::Assigned->value,
            'depreciation_method' => AssetDepreciationMethod::StraightLine->value,
            'useful_life_months' => 48,
            'salvage_value' => '250.00',
        ])
        ->assertSuccessful()
        ->assertJsonPath('asset_code', 'AST-002');
});

it('validates asset update payloads against the current company', function () {
    $company = Company::factory()->create();
    $actor = User::factory()->for($company)->create();
    $asset = Asset::factory()->for($company)->create(['asset_code' => 'AST-003']);
    $otherAsset = Asset::factory()->for($company)->create(['asset_code' => 'AST-004']);
    $otherCategory = AssetCategory::factory()->for(Company::factory())->create();

    $this->actingAs($actor)
        ->patchJson("/test/assets/{$asset->id}", [
            'asset_category_id' => $otherCategory->id,
            'asset_code' => $otherAsset->asset_code,
            'status' => 'missing',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['asset_category_id', 'asset_code', 'status']);

    $this->actingAs($actor)
        ->patchJson("/test/assets/{$asset->id}", [
            'asset_code' => 'AST-003',
            'name_ar' => 'حاسوب محدث',
        ])
        ->assertSuccessful()
        ->assertJsonPath('name_ar', 'حاسوب محدث');
});
