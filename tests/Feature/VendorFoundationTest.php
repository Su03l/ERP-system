<?php

use App\Enums\VendorStatus;
use App\Models\Company;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the vendor foundation schema', function () {
    expect(Schema::hasColumns('vendors', [
        'id',
        'company_id',
        'name_ar',
        'name_en',
        'code',
        'email',
        'phone',
        'tax_number',
        'address',
        'status',
        'metadata',
        'created_at',
        'updated_at',
        'deleted_at',
    ]))->toBeTrue();
});

it('stores vendors with tenant scope and status casts', function () {
    $company = Company::factory()->create();

    $vendor = Vendor::factory()->for($company)->create([
        'name_ar' => 'مورد تجريبي',
        'status' => VendorStatus::Active,
        'metadata' => ['category' => 'supplies'],
    ]);

    expect($vendor->company->is($company))->toBeTrue()
        ->and($company->vendors()->whereKey($vendor)->exists())->toBeTrue()
        ->and($vendor->status)->toBe(VendorStatus::Active)
        ->and($vendor->metadata)->toBe(['category' => 'supplies']);
});

it('scopes vendors to the current company and supports soft deletes', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $vendor = Vendor::factory()->for($company)->create();
    Vendor::factory()->for(Company::factory())->create();

    $this->actingAs($user);

    expect(Vendor::query()->forCurrentCompany()->pluck('id')->all())->toBe([$vendor->id]);

    $vendor->delete();

    expect(Vendor::query()->whereKey($vendor)->exists())->toBeFalse()
        ->and(Vendor::withTrashed()->whereKey($vendor)->exists())->toBeTrue();
});

it('provides localized vendor status labels', function () {
    app()->setLocale('en');

    expect(VendorStatus::Active->label())->toBe('Active')
        ->and(VendorStatus::values())->toBe(['active', 'inactive', 'blocked']);
});
