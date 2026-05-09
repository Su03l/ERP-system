<?php

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Services\AttendanceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance calculator uses company settings to calculate work late overtime and status', function () {
    $company = Company::factory()->create([
        'settings' => [
            'attendance' => [
                'work_start_time' => '08:00',
                'work_end_time' => '16:00',
                'working_days' => ['saturday'],
            ],
        ],
    ]);
    $employee = Employee::factory()->for($company)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($company)->for($employee)->make([
        'attendance_date' => '2026-05-09',
        'clock_in_at' => '2026-05-09 08:15:00',
        'clock_out_at' => '2026-05-09 17:30:00',
    ]);

    $calculated = app(AttendanceCalculator::class)->calculate($attendanceRecord);

    expect($calculated)->toBe([
        'total_work_minutes' => 555,
        'late_minutes' => 15,
        'overtime_minutes' => 90,
        'status' => AttendanceStatus::Late->value,
    ]);
});

test('attendance calculator falls back safely and marks absent or holiday', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $absentRecord = AttendanceRecord::factory()->for($company)->for($employee)->make([
        'attendance_date' => '2026-05-11',
        'clock_in_at' => null,
        'clock_out_at' => null,
    ]);
    $holidayRecord = AttendanceRecord::factory()->for($company)->for($employee)->make([
        'attendance_date' => '2026-05-10',
        'clock_in_at' => null,
        'clock_out_at' => null,
    ]);

    $calculator = app(AttendanceCalculator::class);

    expect($calculator->calculate($absentRecord)['status'])->toBe(AttendanceStatus::Absent->value)
        ->and($calculator->calculate($holidayRecord)['status'])->toBe(AttendanceStatus::Holiday->value)
        ->and($calculator->calculate($absentRecord)['total_work_minutes'])->toBeNull();
});

test('attendance calculator can apply calculated values to a record without saving it', function () {
    $company = Company::factory()->create([
        'settings' => [
            'work_start_time' => '09:00',
            'work_end_time' => '17:00',
            'working_days' => ['monday'],
        ],
    ]);
    $employee = Employee::factory()->for($company)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($company)->for($employee)->make([
        'attendance_date' => '2026-05-11',
        'clock_in_at' => '2026-05-11 09:00:00',
        'clock_out_at' => '2026-05-11 17:10:00',
        'late_minutes' => 99,
    ]);

    app(AttendanceCalculator::class)->apply($attendanceRecord);

    expect($attendanceRecord->status)->toBe(AttendanceStatus::Present)
        ->and($attendanceRecord->late_minutes)->toBe(0)
        ->and($attendanceRecord->overtime_minutes)->toBe(10)
        ->and($attendanceRecord->total_work_minutes)->toBe(490)
        ->and($attendanceRecord->exists)->toBeFalse();
});
