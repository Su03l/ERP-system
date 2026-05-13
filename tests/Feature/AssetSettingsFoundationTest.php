<?php

use App\Enums\AssetDepreciationMethod;
use App\Models\AssetSetting;
use App\Models\Company;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the asset settings schema with tenant ownership fields', function () {
    expect(Schema::hasColumns('asset_settings', [
        'company_id',
        'asset_code_prefix',
        'depreciation_enabled',
        'default_depreciation_method',
        'custody_approval_required',
        'asset_return_approval_required',
        'metadata',
    ]))->toBeTrue();
});

it('stores tenant scoped asset settings with Arabic defaults', function () {
    $company = Company::factory()->create();

    $setting = AssetSetting::factory()->for($company)->create([
        'metadata' => ['managed_by' => 'assets'],
    ]);

    expect($setting->company->is($company))->toBeTrue()
        ->and($company->assetSetting->is($setting))->toBeTrue()
        ->and($setting->asset_code_prefix)->toBe('AST')
        ->and($setting->depreciation_enabled)->toBeTrue()
        ->and($setting->default_depreciation_method)->toBe(AssetDepreciationMethod::StraightLine)
        ->and($setting->custody_approval_required)->toBeTrue()
        ->and($setting->asset_return_approval_required)->toBeTrue()
        ->and($setting->metadata)->toBe(['managed_by' => 'assets']);
});

it('keeps asset settings one to one per company', function () {
    $company = Company::factory()->create();

    AssetSetting::factory()->for($company)->create();
    AssetSetting::factory()->for($company)->create();
})->throws(QueryException::class);

it('provides localized depreciation method labels', function () {
    app()->setLocale('ar');

    expect(AssetDepreciationMethod::StraightLine->label())->toBe('القسط الثابت')
        ->and(AssetDepreciationMethod::DecliningBalance->label())->toBe('الرصيد المتناقص')
        ->and(AssetDepreciationMethod::UnitsOfProduction->label())->toBe('وحدات الإنتاج');
});
