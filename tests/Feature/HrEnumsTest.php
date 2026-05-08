<?php

use App\Enums\DepartmentStatus;
use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Enums\JobTitleStatus;
use App\Enums\WorkType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

test('hr enums expose stable values and localized labels', function () {
    App::setLocale('ar');

    expect(EmployeeStatus::values())->toContain('active', 'inactive', 'on_leave', 'terminated')
        ->and(EmployeeStatus::Active->label())->toBe('نشط')
        ->and(DepartmentStatus::Active->label())->toBe('نشطة')
        ->and(JobTitleStatus::Inactive->label())->toBe('غير نشط')
        ->and(Gender::Female->label())->toBe('أنثى')
        ->and(WorkType::FullTime->label())->toBe('دوام كامل');
});

test('hr enums expose form friendly options', function () {
    App::setLocale('en');

    expect(EmployeeStatus::options())->toContain([
        'value' => 'active',
        'label' => 'Active',
    ])->and(WorkType::options())->toContain([
        'value' => 'hybrid',
        'label' => 'Hybrid',
    ]);
});

test('hr models cast status fields to enums', function () {
    $department = Department::factory()->create();
    $jobTitle = JobTitle::factory()->create();
    $employee = Employee::factory()->create([
        'employment_status' => EmployeeStatus::OnLeave->value,
        'gender' => Gender::Male->value,
        'work_type' => WorkType::Remote->value,
    ]);

    expect($department->status)->toBe(DepartmentStatus::Active)
        ->and($jobTitle->status)->toBe(JobTitleStatus::Active)
        ->and($employee->employment_status)->toBe(EmployeeStatus::OnLeave)
        ->and($employee->gender)->toBe(Gender::Male)
        ->and($employee->work_type)->toBe(WorkType::Remote);
});
