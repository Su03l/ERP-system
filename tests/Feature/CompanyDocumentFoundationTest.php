<?php

use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the company documents schema', function () {
    expect(Schema::hasColumns('company_documents', [
        'company_id',
        'document_type',
        'title_ar',
        'title_en',
        'file_path',
        'issue_date',
        'expiry_date',
        'status',
        'notes_ar',
        'notes_en',
        'metadata',
    ]))->toBeTrue();
});

it('stores tenant scoped company documents', function () {
    $company = Company::factory()->create();

    $document = CompanyDocument::factory()->for($company)->create([
        'title_ar' => 'عقد شركة',
        'metadata' => ['source' => 'manual'],
    ]);

    expect($document->company->is($company))->toBeTrue()
        ->and($company->companyDocuments()->whereKey($document)->exists())->toBeTrue()
        ->and($document->title_ar)->toBe('عقد شركة')
        ->and($document->metadata)->toBe(['source' => 'manual']);
});

it('scopes company document queries to the current company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    CompanyDocument::factory()->for($company)->create();
    CompanyDocument::factory()->for(Company::factory())->create();

    $this->actingAs($user);

    expect(CompanyDocument::query()->forCurrentCompany()->count())->toBe(1);
});
