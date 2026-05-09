<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('employee document belongs to company and employee with localized titles', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $document = EmployeeDocument::factory()->for($company)->for($employee)->create([
        'title_ar' => 'عقد العمل',
        'title_en' => 'Employment Contract',
        'metadata' => ['source' => 'manual'],
    ]);

    expect($document->company->is($company))->toBeTrue()
        ->and($document->employee->is($employee))->toBeTrue()
        ->and($employee->documents()->first()->is($document))->toBeTrue()
        ->and($company->employeeDocuments()->first()->is($document))->toBeTrue()
        ->and($document->title_ar)->toBe('عقد العمل')
        ->and($document->metadata)->toBe(['source' => 'manual']);
});

test('employee documents can be scoped by company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $document = EmployeeDocument::factory()->for($company)->for($employee)->create();

    EmployeeDocument::factory()->for($otherCompany)->for($otherEmployee)->create();

    expect(EmployeeDocument::forCompany($company)->pluck('id')->all())->toBe([$document->id]);
});
