<?php

use App\Enums\AddOnStatus;
use App\Enums\CompanyAddOnStatus;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates marketplace add-on schemas', function () {
    expect(Schema::hasColumns('add_ons', [
        'id',
        'name_ar',
        'name_en',
        'code',
        'description_ar',
        'description_en',
        'category',
        'price_monthly',
        'price_yearly',
        'status',
        'feature_key',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('company_add_ons', [
            'id',
            'company_id',
            'add_on_id',
            'status',
            'starts_at',
            'ends_at',
            'metadata',
            'created_at',
            'updated_at',
        ]))->toBeTrue();
});

it('stores platform add-ons and company-specific add-on activations', function () {
    $company = Company::factory()->create();
    $addOn = AddOn::factory()->create([
        'name_ar' => 'تقارير متقدمة',
        'code' => 'ADVANCED_REPORTS',
        'price_monthly' => '49.50',
        'price_yearly' => '499.00',
        'status' => AddOnStatus::Active,
        'feature_key' => 'advanced_reports',
        'metadata' => ['tier' => 'pro'],
    ]);

    $companyAddOn = CompanyAddOn::factory()->for($company)->create([
        'add_on_id' => $addOn->id,
        'status' => CompanyAddOnStatus::Active,
        'starts_at' => '2026-05-14 00:00:00',
        'metadata' => ['source' => 'manual'],
    ]);

    expect($addOn->status)->toBe(AddOnStatus::Active)
        ->and($addOn->price_monthly)->toBe('49.50')
        ->and($addOn->metadata)->toBe(['tier' => 'pro'])
        ->and($companyAddOn->company->is($company))->toBeTrue()
        ->and($companyAddOn->addOn->is($addOn))->toBeTrue()
        ->and($company->companyAddOns()->whereKey($companyAddOn)->exists())->toBeTrue()
        ->and($addOn->companyAddOns()->whereKey($companyAddOn)->exists())->toBeTrue()
        ->and($companyAddOn->status)->toBe(CompanyAddOnStatus::Active)
        ->and($companyAddOn->metadata)->toBe(['source' => 'manual']);
});

it('scopes company add-ons to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $companyAddOn = CompanyAddOn::factory()->for($company)->create();
    CompanyAddOn::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(CompanyAddOn::query()->forCurrentCompany()->pluck('id')->all())->toBe([$companyAddOn->id]);
});

it('provides localized marketplace add-on status labels', function () {
    app()->setLocale('ar');

    expect(AddOnStatus::Active->label())->toBe('نشطة')
        ->and(CompanyAddOnStatus::Cancelled->label())->toBe('ملغاة')
        ->and(AddOnStatus::values())->toContain('archived')
        ->and(CompanyAddOnStatus::values())->toContain('expired');
});
