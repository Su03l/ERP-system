<?php

use App\Enums\AssetCategoryStatus;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates the asset categories schema with tenant and hierarchy fields', function () {
    expect(Schema::hasColumns('asset_categories', [
        'company_id',
        'parent_id',
        'name_ar',
        'name_en',
        'code',
        'status',
        'description_ar',
        'description_en',
        'metadata',
        'deleted_at',
    ]))->toBeTrue();
});

it('stores hierarchical asset categories for a company', function () {
    $company = Company::factory()->create();
    $parent = AssetCategory::factory()->for($company)->create([
        'name_ar' => 'أجهزة تقنية',
        'code' => 'TECH',
    ]);
    $child = AssetCategory::factory()->for($company)->create([
        'parent_id' => $parent->id,
        'name_ar' => 'أجهزة محمولة',
        'code' => 'LAPTOPS',
        'status' => AssetCategoryStatus::Inactive,
        'metadata' => ['depreciable' => true],
    ]);

    expect($company->assetCategories()->whereKey($parent)->exists())->toBeTrue()
        ->and($parent->children()->whereKey($child)->exists())->toBeTrue()
        ->and($child->parent->is($parent))->toBeTrue()
        ->and($child->status)->toBe(AssetCategoryStatus::Inactive)
        ->and($child->metadata)->toBe(['depreciable' => true]);
});

it('prevents parent categories from another company', function () {
    $company = Company::factory()->create();
    $otherParent = AssetCategory::factory()->for(Company::factory())->create();

    AssetCategory::factory()->for($company)->create([
        'parent_id' => $otherParent->id,
    ]);
})->throws(ValidationException::class);

it('prevents self parent assignment', function () {
    $category = AssetCategory::factory()->create();

    $category->update(['parent_id' => $category->id]);
})->throws(ValidationException::class);

it('keeps category codes unique per company only', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    AssetCategory::factory()->for($company)->create(['code' => 'VEH']);
    AssetCategory::factory()->for($otherCompany)->create(['code' => 'VEH']);

    AssetCategory::factory()->for($company)->create(['code' => 'VEH']);
})->throws(QueryException::class);

it('scopes asset category queries to the current company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    AssetCategory::factory()->for($company)->create();
    AssetCategory::factory()->for(Company::factory())->create();

    $this->actingAs($user);

    expect(AssetCategory::query()->forCurrentCompany()->count())->toBe(1);
});

it('provides localized asset category status labels', function () {
    app()->setLocale('ar');

    expect(AssetCategoryStatus::Active->label())->toBe('نشط')
        ->and(AssetCategoryStatus::Inactive->label())->toBe('غير نشط');
});
