<?php

use App\Enums\AccountNormalBalance;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\Company;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores tenant scoped accounting settings with Arabic defaults', function () {
    $company = Company::factory()->create();

    $setting = AccountingSetting::factory()->for($company)->create([
        'tax_enabled' => true,
        'default_vat_rate' => 15,
    ]);

    expect($setting->company->is($company))->toBeTrue()
        ->and($company->accountingSetting->is($setting))->toBeTrue()
        ->and($setting->fiscal_year_start_month)->toBe(1)
        ->and($setting->default_currency)->toBe('SAR')
        ->and($setting->tax_enabled)->toBeTrue()
        ->and($setting->default_vat_rate)->toBe('15.00')
        ->and($setting->invoice_numbering_prefix)->toBe('INV')
        ->and($setting->journal_numbering_prefix)->toBe('JRN')
        ->and($setting->accounting_approval_required)->toBeTrue();
});

it('stores hierarchical chart of accounts with enum backed fields', function () {
    $company = Company::factory()->create();
    $parent = Account::factory()->for($company)->create([
        'code' => '1000',
        'name_ar' => 'الأصول',
        'type' => AccountType::Asset,
        'normal_balance' => AccountNormalBalance::Debit,
        'level' => 1,
        'is_system' => true,
    ]);
    $child = Account::factory()->for($company)->create([
        'parent_id' => $parent->id,
        'code' => '1100',
        'name_ar' => 'النقدية',
        'type' => AccountType::Asset,
        'normal_balance' => AccountNormalBalance::Debit,
        'level' => 2,
    ]);

    expect($company->accounts()->whereKey($parent)->exists())->toBeTrue()
        ->and($parent->children()->whereKey($child)->exists())->toBeTrue()
        ->and($child->parent->is($parent))->toBeTrue()
        ->and($child->type)->toBe(AccountType::Asset)
        ->and($child->normal_balance)->toBe(AccountNormalBalance::Debit)
        ->and($child->is_active)->toBeTrue()
        ->and($parent->is_system)->toBeTrue();
});

it('prevents duplicate account codes inside the same company only', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    Account::factory()->for($company)->create(['code' => '1000']);
    Account::factory()->for($otherCompany)->create(['code' => '1000']);

    Account::factory()->for($company)->create(['code' => '1000']);
})->throws(QueryException::class);

it('provides localized accounting enum labels', function () {
    app()->setLocale('ar');

    expect(AccountType::Asset->label())->toBe('أصل')
        ->and(AccountType::Expense->label())->toBe('مصروف')
        ->and(AccountNormalBalance::Debit->label())->toBe('مدين')
        ->and(AccountNormalBalance::Credit->label())->toBe('دائن');
});
