<?php

use App\Enums\AssetDepreciationMethod;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates the assets schema with tenant ownership and depreciation fields', function () {
    expect(Schema::hasColumns('assets', [
        'company_id',
        'asset_category_id',
        'asset_code',
        'name_ar',
        'name_en',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'status',
        'location',
        'assigned_employee_id',
        'depreciation_method',
        'useful_life_months',
        'salvage_value',
        'metadata',
        'deleted_at',
    ]))->toBeTrue();
});

it('stores tenant scoped assets with category and employee relationships', function () {
    $company = Company::factory()->create();
    $category = AssetCategory::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    $asset = Asset::factory()->for($company)->create([
        'asset_category_id' => $category->id,
        'asset_code' => 'AST-0001',
        'name_ar' => 'حاسوب محمول',
        'assigned_employee_id' => $employee->id,
        'purchase_cost' => 5000,
        'current_value' => 4000,
        'status' => AssetStatus::Assigned,
        'depreciation_method' => AssetDepreciationMethod::StraightLine,
        'metadata' => ['source' => 'manual'],
    ]);

    expect($company->assets()->whereKey($asset)->exists())->toBeTrue()
        ->and($category->assets()->whereKey($asset)->exists())->toBeTrue()
        ->and($employee->assignedAssets()->whereKey($asset)->exists())->toBeTrue()
        ->and($asset->category->is($category))->toBeTrue()
        ->and($asset->assignedEmployee->is($employee))->toBeTrue()
        ->and($asset->purchase_cost)->toBe('5000.00')
        ->and($asset->current_value)->toBe('4000.00')
        ->and($asset->status)->toBe(AssetStatus::Assigned)
        ->and($asset->depreciation_method)->toBe(AssetDepreciationMethod::StraightLine)
        ->and($asset->metadata)->toBe(['source' => 'manual']);
});

it('prevents assigning an asset category from another company', function () {
    $company = Company::factory()->create();
    $otherCategory = AssetCategory::factory()->for(Company::factory())->create();

    Asset::factory()->for($company)->create([
        'asset_category_id' => $otherCategory->id,
    ]);
})->throws(ValidationException::class);

it('prevents assigning an employee from another company', function () {
    $company = Company::factory()->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();

    Asset::factory()->for($company)->create([
        'assigned_employee_id' => $otherEmployee->id,
    ]);
})->throws(ValidationException::class);

it('keeps asset codes unique per company only', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    Asset::factory()->for($company)->create(['asset_code' => 'AST-0001']);
    Asset::factory()->for($otherCompany)->create(['asset_code' => 'AST-0001']);

    Asset::factory()->for($company)->create(['asset_code' => 'AST-0001']);
})->throws(QueryException::class);

it('scopes asset queries to the current company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    Asset::factory()->for($company)->create();
    Asset::factory()->for(Company::factory())->create();

    $this->actingAs($user);

    expect(Asset::query()->forCurrentCompany()->count())->toBe(1);
});

it('provides localized asset status labels', function () {
    app()->setLocale('ar');

    expect(AssetStatus::Available->label())->toBe('متاح')
        ->and(AssetStatus::Assigned->label())->toBe('مخصص')
        ->and(AssetStatus::UnderMaintenance->label())->toBe('تحت الصيانة');
});
