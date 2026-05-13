<?php

use App\Enums\DocumentOwnerType;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\CompanyDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('provides stable document enum values', function () {
    expect(DocumentStatus::values())->toBe([
        'active',
        'expired',
        'pending_approval',
        'approved',
        'rejected',
        'archived',
    ])
        ->and(DocumentType::values())->toContain('contract', 'license', 'certificate', 'policy', 'identification', 'national_id', 'passport', 'other')
        ->and(DocumentOwnerType::values())->toContain('company', 'employee', 'asset', 'vendor', 'customer');
});

it('provides localized document enum labels', function () {
    app()->setLocale('ar');

    expect(DocumentStatus::PendingApproval->label())->toBe('بانتظار الاعتماد')
        ->and(DocumentType::Contract->label())->toBe('عقد')
        ->and(DocumentOwnerType::Company->label())->toBe('شركة');
});

it('casts company document status and type enums', function () {
    $document = CompanyDocument::factory()->create([
        'document_type' => DocumentType::License,
        'status' => DocumentStatus::Active,
    ]);

    expect($document->document_type)->toBe(DocumentType::License)
        ->and($document->status)->toBe(DocumentStatus::Active);
});
