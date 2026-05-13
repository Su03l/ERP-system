<?php

use App\Enums\AssetDepreciationMethod;
use App\Enums\DepreciationScheduleStatus;
use App\Models\Asset;
use App\Models\Company;
use App\Models\DepreciationSchedule;
use App\Services\AssetDepreciationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates the depreciation schedules schema', function () {
    expect(Schema::hasColumns('depreciation_schedules', [
        'company_id',
        'asset_id',
        'period_date',
        'depreciation_amount',
        'accumulated_depreciation',
        'book_value',
        'status',
        'posted_journal_entry_id',
        'metadata',
    ]))->toBeTrue();
});

it('calculates straight line depreciation values', function () {
    $asset = Asset::factory()->create([
        'purchase_date' => '2026-01-15',
        'purchase_cost' => '12000.00',
        'salvage_value' => '0.00',
        'useful_life_months' => 12,
        'depreciation_method' => AssetDepreciationMethod::StraightLine,
    ]);

    $result = app(AssetDepreciationService::class)->calculate($asset, Carbon::parse('2026-03-31'));

    expect($result)->toBe([
        'depreciation_amount' => '1000.00',
        'accumulated_depreciation' => '3000.00',
        'book_value' => '9000.00',
    ]);
});

it('stores tenant scoped depreciation schedules', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create();

    $schedule = DepreciationSchedule::factory()->for($company)->create([
        'asset_id' => $asset->id,
        'period_date' => '2026-05-31',
        'depreciation_amount' => '100.00',
        'accumulated_depreciation' => '500.00',
        'book_value' => '9500.00',
        'status' => DepreciationScheduleStatus::Calculated,
    ]);

    expect($company->depreciationSchedules()->whereKey($schedule)->exists())->toBeTrue()
        ->and($asset->depreciationSchedules()->whereKey($schedule)->exists())->toBeTrue()
        ->and($schedule->status)->toBe(DepreciationScheduleStatus::Calculated)
        ->and($schedule->depreciation_amount)->toBe('100.00');
});

it('prevents depreciation schedules for assets from another company', function () {
    $company = Company::factory()->create();
    $otherAsset = Asset::factory()->for(Company::factory())->create();

    DepreciationSchedule::factory()->for($company)->create([
        'asset_id' => $otherAsset->id,
    ]);
})->throws(ValidationException::class);
