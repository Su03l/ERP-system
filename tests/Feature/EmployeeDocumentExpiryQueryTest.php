<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\EmployeeDocumentExpiryQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('employee document expiry query finds expired and expiring documents for current company', function () {
    Carbon::setTestNow('2026-05-09');

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();

    $expired = EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => '2026-05-08']);
    $expiringSoon = EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => '2026-05-20']);
    EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => '2026-07-01']);
    EmployeeDocument::factory()->for($otherCompany)->for($otherEmployee)->create(['expiry_date' => '2026-05-15']);

    $this->actingAs($user);

    $query = app(EmployeeDocumentExpiryQuery::class);

    expect($query->expiredForCurrentCompany()->pluck('id')->all())->toBe([$expired->id])
        ->and($query->expiringWithinForCurrentCompany(30)->pluck('id')->all())->toBe([$expiringSoon->id]);

    Carbon::setTestNow();
});

test('employee document expiry query can group counts by company', function () {
    Carbon::setTestNow('2026-05-09');

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();

    EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => '2026-05-08']);
    EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => '2026-05-20']);
    EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => '2026-07-01']);
    EmployeeDocument::factory()->for($otherCompany)->for($otherEmployee)->create(['expiry_date' => '2026-05-10']);

    $counts = app(EmployeeDocumentExpiryQuery::class)
        ->countsByCompany(30)
        ->keyBy('company_id');

    expect((int) $counts[$company->id]->expired_count)->toBe(1)
        ->and((int) $counts[$company->id]->expiring_count)->toBe(1)
        ->and((int) $counts[$otherCompany->id]->expired_count)->toBe(0)
        ->and((int) $counts[$otherCompany->id]->expiring_count)->toBe(1);

    Carbon::setTestNow();
});

test('current company expiry queries return no records without tenant context', function () {
    Carbon::setTestNow('2026-05-09');

    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();

    EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => '2026-05-10']);

    $query = app(EmployeeDocumentExpiryQuery::class);

    expect($query->expiringWithinForCurrentCompany(30)->exists())->toBeFalse()
        ->and($query->expiredForCurrentCompany()->exists())->toBeFalse();

    Carbon::setTestNow();
});
