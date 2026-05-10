<?php

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('attendance metrics are scoped to current company and support date and department filters', function () {
    Carbon::setTestNow('2026-05-10');

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();
    $otherDepartment = Department::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->for($department)->create();
    $otherDepartmentEmployee = Employee::factory()->for($company)->for($otherDepartment)->create();

    AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-01',
        'status' => AttendanceStatus::Present->value,
        'overtime_minutes' => 30,
        'total_work_minutes' => 480,
    ]);
    AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-02',
        'status' => AttendanceStatus::Late->value,
        'overtime_minutes' => 60,
        'total_work_minutes' => 420,
    ]);
    AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-03',
        'status' => AttendanceStatus::Absent->value,
        'overtime_minutes' => 0,
        'total_work_minutes' => null,
    ]);
    AttendanceRecord::factory()->for($company)->for($otherDepartmentEmployee)->create([
        'attendance_date' => '2026-05-04',
        'status' => AttendanceStatus::Present->value,
    ]);
    AttendanceRecord::factory()->for($otherCompany)->create([
        'attendance_date' => '2026-05-01',
        'status' => AttendanceStatus::Absent->value,
    ]);

    $this->actingAs($user);

    $metrics = app(AttendanceMetrics::class)->forCurrentCompany([
        'department_id' => $department->id,
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-31',
    ]);

    expect($metrics['date_range'])->toBe(['start' => '2026-05-01', 'end' => '2026-05-31'])
        ->and($metrics['present_count']['value'])->toBe(1)
        ->and($metrics['absent_count']['value'])->toBe(1)
        ->and($metrics['late_count']['value'])->toBe(1)
        ->and($metrics['overtime_total']['value'])->toBe(90)
        ->and($metrics['average_work_hours']['value'])->toBe(7.5)
        ->and($metrics['attendance_rate']['value'])->toBe(66.67)
        ->and($metrics['late_rate']['value'])->toBe(33.33)
        ->and($metrics['absence_rate']['value'])->toBe(33.33);

    Carbon::setTestNow();
});

test('attendance metrics support explicit company and empty tenant context', function () {
    Carbon::setTestNow('2026-05-10');

    $company = Company::factory()->create();
    AttendanceRecord::factory()->for($company)->create([
        'attendance_date' => '2026-05-05',
        'status' => AttendanceStatus::Present->value,
        'total_work_minutes' => 600,
    ]);

    $metrics = app(AttendanceMetrics::class)->forCompany($company, [
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-31',
    ]);
    $emptyMetrics = app(AttendanceMetrics::class)->forCurrentCompany([
        'date_from' => '2026-05-01',
        'date_to' => '2026-05-31',
    ]);

    expect($metrics['present_count']['value'])->toBe(1)
        ->and($metrics['average_work_hours']['value'])->toBe(10.0)
        ->and($emptyMetrics['present_count']['value'])->toBe(0)
        ->and($emptyMetrics['attendance_rate']['value'])->toBe(0.0);

    Carbon::setTestNow();
});
