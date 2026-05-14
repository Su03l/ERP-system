<?php

use App\DTOs\KpiDateRange;
use App\Enums\EmployeeStatus;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\Kpis\Hr\ActiveEmployeesKpi;
use App\Services\Kpis\Hr\DocumentsExpiringSoonKpi;
use App\Services\Kpis\Hr\EmployeesByDepartmentKpi;
use App\Services\Kpis\Hr\InactiveEmployeesKpi;
use App\Services\Kpis\Hr\NewHiresKpi;
use App\Services\Kpis\Hr\TotalEmployeesKpi;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves HR employee KPI values for a company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $department = Department::factory()->for($company)->create(['name_ar' => 'الموارد البشرية']);
    Employee::factory()->for($company)->count(2)->create([
        'employment_status' => EmployeeStatus::Active,
        'department_id' => $department->id,
        'hire_date' => '2026-01-10',
    ]);
    Employee::factory()->for($company)->create([
        'employment_status' => EmployeeStatus::Inactive,
        'hire_date' => '2025-12-10',
    ]);
    Employee::factory()->for($otherCompany)->count(5)->create();

    $dateRange = KpiDateRange::fromDates('2026-01-01', '2026-01-31');

    expect(app(TotalEmployeesKpi::class)->resolve($company, $dateRange)->value)->toBe(3)
        ->and(app(ActiveEmployeesKpi::class)->resolve($company, $dateRange)->value)->toBe(2)
        ->and(app(InactiveEmployeesKpi::class)->resolve($company, $dateRange)->value)->toBe(1)
        ->and(app(NewHiresKpi::class)->resolve($company, $dateRange)->value)->toBe(2)
        ->and(app(EmployeesByDepartmentKpi::class)->resolve($company, $dateRange)->metadata['values'][0]['value'])->toBe(2);
});

it('resolves employee documents expiring soon KPI', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => now()->addDays(5)]);
    EmployeeDocument::factory()->for($company)->for($employee)->create(['expiry_date' => now()->addDays(45)]);

    $result = app(DocumentsExpiringSoonKpi::class)->resolve(
        $company,
        KpiDateRange::fromDates(now()->toDateString(), now()->addDays(30)->toDateString()),
    );

    expect($result->value)->toBe(1)
        ->and($result->unit)->toBe('documents');
});
