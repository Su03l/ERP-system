<?php

use App\Enums\CustomerStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the customer foundation schema', function () {
    expect(Schema::hasColumns('customers', [
        'id',
        'company_id',
        'name_ar',
        'name_en',
        'code',
        'email',
        'phone',
        'tax_number',
        'billing_address',
        'status',
        'metadata',
        'created_at',
        'updated_at',
        'deleted_at',
    ]))->toBeTrue();
});

it('stores customers with tenant scope localized names and casts', function () {
    $company = Company::factory()->create();

    $customer = Customer::factory()->for($company)->create([
        'name_ar' => 'عميل تجريبي',
        'name_en' => 'Test Customer',
        'status' => CustomerStatus::Active,
        'metadata' => ['segment' => 'sales'],
    ]);

    expect($customer->company_id)->toBe($company->id)
        ->and($customer->company->is($company))->toBeTrue()
        ->and($company->customers()->whereKey($customer)->exists())->toBeTrue()
        ->and($customer->status)->toBe(CustomerStatus::Active)
        ->and($customer->metadata)->toBe(['segment' => 'sales']);
});

it('scopes customers to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $customer = Customer::factory()->for($company)->create();
    Customer::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(Customer::query()->forCurrentCompany()->pluck('id')->all())->toBe([$customer->id]);
});

it('supports soft deleting customers', function () {
    $customer = Customer::factory()->create();

    $customer->delete();

    expect(Customer::query()->whereKey($customer)->exists())->toBeFalse()
        ->and(Customer::withTrashed()->whereKey($customer)->exists())->toBeTrue();
});

it('provides localized customer status labels', function () {
    app()->setLocale('en');

    expect(CustomerStatus::Active->label())->toBe('Active')
        ->and(CustomerStatus::values())->toBe(['active', 'inactive', 'blocked']);
});
