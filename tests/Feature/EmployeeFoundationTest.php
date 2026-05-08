<?php

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('employee belongs to a company and stores localized names', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create([
        'employee_number' => 'EMP-001',
        'first_name_ar' => 'سارة',
        'last_name_ar' => 'أحمد',
        'first_name_en' => 'Sara',
        'last_name_en' => 'Ahmed',
    ]);

    expect($employee->company->is($company))->toBeTrue()
        ->and($company->employees()->first()->is($employee))->toBeTrue()
        ->and($employee->first_name_ar)->toBe('سارة')
        ->and($employee->last_name_en)->toBe('Ahmed');
});

test('employee supports user department job title and manager relationships', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();
    $jobTitle = JobTitle::factory()->for($company)->create();
    $manager = Employee::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'job_title_id' => $jobTitle->id,
        'manager_id' => $manager->id,
    ]);

    expect($employee->user->is($user))->toBeTrue()
        ->and($user->employeeProfile->is($employee))->toBeTrue()
        ->and($employee->department->is($department))->toBeTrue()
        ->and($department->employees()->first()->is($employee))->toBeTrue()
        ->and($employee->jobTitle->is($jobTitle))->toBeTrue()
        ->and($jobTitle->employees()->first()->is($employee))->toBeTrue()
        ->and($employee->manager->is($manager))->toBeTrue()
        ->and($manager->directReports()->first()->is($employee))->toBeTrue();
});

test('employees can be scoped by company', function () {
    $firstCompany = Company::factory()->create();
    $secondCompany = Company::factory()->create();
    $firstEmployee = Employee::factory()->for($firstCompany)->create();

    Employee::factory()->for($secondCompany)->create();

    expect(Employee::forCompany($firstCompany)->pluck('id')->all())->toBe([$firstEmployee->id]);
});

test('employee salary is stored as a decimal cast only', function () {
    $employee = Employee::factory()->create([
        'basic_salary' => '12345.67',
    ]);

    expect($employee->basic_salary)->toBe('12345.67');
});
