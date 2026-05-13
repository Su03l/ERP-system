<?php

use App\Enums\CustodyStatus;
use App\Models\Asset;
use App\Models\AssetCustody;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates the asset custodies schema', function () {
    expect(Schema::hasColumns('asset_custodies', [
        'company_id',
        'asset_id',
        'employee_id',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'return_received_by',
        'status',
        'notes_ar',
        'notes_en',
        'workflow_instance_id',
        'metadata',
    ]))->toBeTrue();
});

it('stores tenant scoped asset custody relationships', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    $custody = AssetCustody::factory()->for($company)->create([
        'asset_id' => $asset->id,
        'employee_id' => $employee->id,
        'status' => CustodyStatus::Pending,
        'metadata' => ['workflow' => 'later'],
    ]);

    expect($company->assetCustodies()->whereKey($custody)->exists())->toBeTrue()
        ->and($asset->custodies()->whereKey($custody)->exists())->toBeTrue()
        ->and($employee->assetCustodies()->whereKey($custody)->exists())->toBeTrue()
        ->and($custody->asset->is($asset))->toBeTrue()
        ->and($custody->employee->is($employee))->toBeTrue()
        ->and($custody->status)->toBe(CustodyStatus::Pending)
        ->and($custody->metadata)->toBe(['workflow' => 'later']);
});

it('prevents asset custody records across companies', function () {
    $company = Company::factory()->create();
    $otherAsset = Asset::factory()->for(Company::factory())->create();
    $employee = Employee::factory()->for($company)->create();

    AssetCustody::factory()->for($company)->create([
        'asset_id' => $otherAsset->id,
        'employee_id' => $employee->id,
    ]);
})->throws(ValidationException::class);

it('prevents custody employees from another company', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for(Company::factory())->create();

    AssetCustody::factory()->for($company)->create([
        'asset_id' => $asset->id,
        'employee_id' => $otherEmployee->id,
    ]);
})->throws(ValidationException::class);
