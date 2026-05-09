<?php

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

test('attendance enums expose stable values and localized labels', function () {
    App::setLocale('ar');

    expect(AttendanceStatus::values())->toContain('present', 'absent', 'late', 'on_leave', 'holiday', 'remote')
        ->and(AttendanceSource::values())->toContain('manual', 'web', 'device', 'import')
        ->and(AttendanceStatus::Late->label())->toBe('متأخر')
        ->and(AttendanceSource::Web->label())->toBe('الويب');
});

test('attendance enums expose form friendly options', function () {
    App::setLocale('en');

    expect(AttendanceStatus::options())->toContain([
        'value' => 'present',
        'label' => 'Present',
    ])->and(AttendanceSource::options())->toContain([
        'value' => 'device',
        'label' => 'Device',
    ]);
});

test('attendance record casts status and source to enums', function () {
    $attendanceRecord = AttendanceRecord::factory()->create([
        'status' => AttendanceStatus::Late->value,
        'source' => AttendanceSource::Web->value,
    ]);

    expect($attendanceRecord->status)->toBe(AttendanceStatus::Late)
        ->and($attendanceRecord->source)->toBe(AttendanceSource::Web);
});
