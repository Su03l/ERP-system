<?php

use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\DocumentExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('detects expired and expiring employee and company documents for current company', function () {
    Carbon::setTestNow('2026-05-13');
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $user = User::factory()->for($company)->create();
    $otherCompany = Company::factory()->create();

    EmployeeDocument::factory()->for($company)->create(['employee_id' => $employee->id, 'expiry_date' => '2026-05-12']);
    EmployeeDocument::factory()->for($company)->create(['employee_id' => $employee->id, 'expiry_date' => '2026-05-20']);
    CompanyDocument::factory()->for($company)->create(['expiry_date' => '2026-05-18']);
    CompanyDocument::factory()->for($company)->create(['expiry_date' => '2026-05-01']);
    CompanyDocument::factory()->for($otherCompany)->create(['expiry_date' => '2026-05-18']);

    $this->actingAs($user);

    $service = app(DocumentExpiryService::class);
    $expired = $service->expiredForCurrentCompany();
    $expiring = $service->expiringWithinForCurrentCompany(10);

    expect($expired['employee_documents'])->toHaveCount(1)
        ->and($expired['company_documents'])->toHaveCount(1)
        ->and($expiring['employee_documents'])->toHaveCount(1)
        ->and($expiring['company_documents'])->toHaveCount(1);
});

it('groups document expiry counts by company', function () {
    Carbon::setTestNow('2026-05-13');
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();

    EmployeeDocument::factory()->for($company)->create(['employee_id' => $employee->id, 'expiry_date' => '2026-05-12']);
    CompanyDocument::factory()->for($company)->create(['expiry_date' => '2026-05-18']);

    $counts = app(DocumentExpiryService::class)->countsByCompany(10);

    expect($counts)->toHaveCount(1)
        ->and($counts[0]['company_id'])->toBe($company->id)
        ->and($counts[0]['expired_count'])->toBe(1)
        ->and($counts[0]['expiring_count'])->toBe(1);
});
