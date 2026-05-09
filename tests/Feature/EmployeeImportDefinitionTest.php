<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Services\EmployeeImportDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('employee import definition exposes employee entity columns with arabic aliases', function () {
    $definition = app(EmployeeImportDefinition::class)->definition();

    expect($definition['entity_type'])->toBe('employees')
        ->and($definition['module_key'])->toBe('hr')
        ->and(collect($definition['columns'])->pluck('key')->all())->toContain('employee_number', 'first_name_ar', 'last_name_ar', 'department', 'job_title')
        ->and($definition['columns'][0]['aliases'])->toContain('رقم الموظف');
});

test('employee import rows validate and map tenant scoped department and job title', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $department = Department::factory()->for($company)->create(['name_ar' => 'الموارد البشرية', 'code' => 'HR']);
    $jobTitle = JobTitle::factory()->for($company)->create(['name_ar' => 'محاسب', 'code' => 'ACC']);
    Department::factory()->for($otherCompany)->create(['name_ar' => 'تقنية المعلومات', 'code' => 'IT']);

    $result = app(EmployeeImportDefinition::class)->validateRow([
        'employee_number' => 'EMP-900',
        'first_name_ar' => 'سارة',
        'last_name_ar' => 'أحمد',
        'email' => 'sara@example.com',
        'department' => 'HR',
        'job_title' => 'محاسب',
        'hire_date' => '2026-05-01',
        'basic_salary' => 12000,
    ], $company);

    expect($result['valid'])->toBeTrue()
        ->and($result['data']['department_id'])->toBe($department->id)
        ->and($result['data']['job_title_id'])->toBe($jobTitle->id)
        ->and($result['data'])->not->toHaveKey('company_id');
});

test('employee import validation rejects duplicate employee numbers and cross tenant lookups', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    Employee::factory()->for($company)->create(['employee_number' => 'EMP-901']);
    Department::factory()->for($otherCompany)->create(['code' => 'FIN']);
    JobTitle::factory()->for($otherCompany)->create(['code' => 'MGR']);

    $result = app(EmployeeImportDefinition::class)->validateRow([
        'employee_number' => 'EMP-901',
        'first_name_ar' => 'ليان',
        'last_name_ar' => 'سالم',
        'email' => 'not-email',
        'department' => 'FIN',
        'job_title' => 'MGR',
        'basic_salary' => -1,
    ], $company);

    expect($result['valid'])->toBeFalse()
        ->and($result['errors'])->toHaveKeys(['employee_number', 'email', 'department', 'job_title', 'basic_salary']);
});

test('employee import preview limits rows and includes row level validation output', function () {
    $company = Company::factory()->create();

    $preview = app(EmployeeImportDefinition::class)->preview([
        [
            'employee_number' => 'EMP-902',
            'first_name_ar' => 'محمد',
            'last_name_ar' => 'علي',
        ],
        [
            'employee_number' => '',
            'first_name_ar' => '',
            'last_name_ar' => '',
        ],
    ], $company, 1);

    expect($preview['entity_type'])->toBe('employees')
        ->and($preview['total_rows'])->toBe(2)
        ->and($preview['preview_rows'])->toHaveCount(1)
        ->and($preview['preview_rows'][0]['row_number'])->toBe(1)
        ->and($preview['preview_rows'][0]['valid'])->toBeTrue();
});
