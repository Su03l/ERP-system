<?php

use App\Models\Company;
use App\Models\DocumentSetting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the document settings schema', function () {
    expect(Schema::hasColumns('document_settings', [
        'company_id',
        'default_expiry_reminder_days',
        'allowed_file_types',
        'max_file_size',
        'document_approval_required',
        'metadata',
    ]))->toBeTrue();
});

it('stores tenant scoped document settings', function () {
    $company = Company::factory()->create();

    $setting = DocumentSetting::factory()->for($company)->create([
        'allowed_file_types' => ['pdf'],
        'metadata' => ['storage' => 'local'],
    ]);

    expect($setting->company->is($company))->toBeTrue()
        ->and($company->documentSetting->is($setting))->toBeTrue()
        ->and($setting->default_expiry_reminder_days)->toBe(30)
        ->and($setting->allowed_file_types)->toBe(['pdf'])
        ->and($setting->document_approval_required)->toBeTrue()
        ->and($setting->metadata)->toBe(['storage' => 'local']);
});

it('keeps document settings one to one per company', function () {
    $company = Company::factory()->create();

    DocumentSetting::factory()->for($company)->create();
    DocumentSetting::factory()->for($company)->create();
})->throws(QueryException::class);
