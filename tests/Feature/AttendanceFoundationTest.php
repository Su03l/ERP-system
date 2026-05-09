<?php

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance record belongs to company and employee with tenant scope', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-09',
        'clock_in_at' => '2026-05-09 08:00:00',
        'clock_out_at' => '2026-05-09 17:00:00',
        'clock_in_ip' => '127.0.0.1',
        'status' => 'present',
        'source' => 'web',
        'late_minutes' => 5,
        'overtime_minutes' => 30,
        'total_work_minutes' => 540,
        'metadata' => ['device' => 'browser'],
    ]);

    AttendanceRecord::factory()->for($otherCompany)->for($otherEmployee)->create();

    expect($attendanceRecord->company->is($company))->toBeTrue()
        ->and($attendanceRecord->employee->is($employee))->toBeTrue()
        ->and($company->attendanceRecords()->first()->is($attendanceRecord))->toBeTrue()
        ->and($employee->attendanceRecords()->first()->is($attendanceRecord))->toBeTrue()
        ->and($attendanceRecord->attendance_date->toDateString())->toBe('2026-05-09')
        ->and($attendanceRecord->metadata)->toBe(['device' => 'browser'])
        ->and(AttendanceRecord::forCompany($company)->pluck('id')->all())->toBe([$attendanceRecord->id]);
});

test('attendance records are unique per employee attendance date inside company', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();

    AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-09',
    ]);

    AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-09',
    ]);
})->throws(QueryException::class);
