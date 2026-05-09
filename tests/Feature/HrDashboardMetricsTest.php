<?php

use App\Enums\EmployeeStatus;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Services\HrDashboardMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('hr dashboard metrics are scoped to current company', function () {
    Carbon::setTestNow('2026-05-09');

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create(['name_ar' => 'الموارد البشرية']);
    $otherDepartment = Department::factory()->for($otherCompany)->create(['name_ar' => 'الموارد البشرية']);

    $activeEmployee = Employee::factory()->for($company)->for($department)->create([
        'employment_status' => EmployeeStatus::Active->value,
        'hire_date' => '2026-05-01',
    ]);
    Employee::factory()->for($company)->for($department)->create([
        'employment_status' => EmployeeStatus::Inactive->value,
        'hire_date' => '2026-04-01',
    ]);
    Employee::factory()->for($otherCompany)->for($otherDepartment)->create([
        'employment_status' => EmployeeStatus::Active->value,
        'hire_date' => '2026-05-01',
    ]);
    EmployeeDocument::factory()->for($company)->for($activeEmployee)->create(['expiry_date' => '2026-05-20']);
    EmployeeDocument::factory()->for($otherCompany)->create(['expiry_date' => '2026-05-20']);

    $this->actingAs($user);

    $metrics = app(HrDashboardMetrics::class)->forCurrentCompany(documentExpiryDays: 30);

    expect($metrics['total_employees']['value'])->toBe(2)
        ->and($metrics['active_employees']['value'])->toBe(1)
        ->and($metrics['inactive_employees']['value'])->toBe(1)
        ->and($metrics['new_hires']['value'])->toBe(1)
        ->and($metrics['documents_expiring_soon']['value'])->toBe(1)
        ->and($metrics['employees_by_department']['values'])->toHaveCount(1)
        ->and($metrics['employees_by_department']['values'][0]['value'])->toBe(2)
        ->and($metrics['employees_by_status']['values'])->toHaveCount(2);

    Carbon::setTestNow();
});

test('hr dashboard metrics support custom new hire date range and no tenant context', function () {
    Carbon::setTestNow('2026-05-09');

    $company = Company::factory()->create();
    Employee::factory()->for($company)->create([
        'employment_status' => EmployeeStatus::Active->value,
        'hire_date' => '2026-04-15',
    ]);

    $metrics = app(HrDashboardMetrics::class)->forCompany(
        $company,
        Carbon::parse('2026-04-01'),
        Carbon::parse('2026-04-30'),
    );
    $emptyMetrics = app(HrDashboardMetrics::class)->forCurrentCompany();

    expect($metrics['new_hires']['value'])->toBe(1)
        ->and($metrics['new_hires']['date_range'])->toBe(['start' => '2026-04-01', 'end' => '2026-04-30'])
        ->and($emptyMetrics['total_employees']['value'])->toBe(0);

    Carbon::setTestNow();
});
