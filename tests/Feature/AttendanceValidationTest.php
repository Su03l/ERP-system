<?php

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Http\Requests\ManualClockAttendanceRequest;
use App\Http\Requests\StoreAttendanceRecordRequest;
use App\Http\Requests\UpdateAttendanceRecordRequest;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function validAttendancePayload(array $overrides = []): array
{
    return array_merge([
        'attendance_date' => '2026-05-09',
        'clock_in_at' => '2026-05-09 08:00:00',
        'clock_out_at' => '2026-05-09 17:00:00',
        'clock_in_ip' => '127.0.0.1',
        'clock_out_ip' => '127.0.0.1',
        'status' => AttendanceStatus::Present->value,
        'source' => AttendanceSource::Manual->value,
        'late_minutes' => 0,
        'overtime_minutes' => 0,
        'total_work_minutes' => 540,
    ], $overrides);
}

test('store attendance request validates tenant employee enum and duplicate date', function () {
    Route::post('/attendance-validation-store', fn (StoreAttendanceRecordRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();

    AttendanceRecord::factory()->for($company)->for($employee)->create(['attendance_date' => '2026-05-09']);

    $this->actingAs($user)
        ->postJson('/attendance-validation-store', validAttendancePayload(['employee_id' => $otherEmployee->id]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['employee_id']);

    $this->actingAs($user)
        ->postJson('/attendance-validation-store', validAttendancePayload([
            'employee_id' => $employee->id,
            'status' => 'invalid',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['attendance_date', 'status']);
});

test('store attendance request validates clock out after clock in', function () {
    Route::post('/attendance-validation-clock-order', fn (StoreAttendanceRecordRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();

    $this->actingAs($user)
        ->postJson('/attendance-validation-clock-order', validAttendancePayload([
            'employee_id' => $employee->id,
            'clock_in_at' => '2026-05-09 17:00:00',
            'clock_out_at' => '2026-05-09 08:00:00',
        ]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['clock_out_at']);
});

test('update attendance request prevents duplicate effective employee date', function () {
    Route::patch('/attendance-validation-update/{attendance_record}', fn (UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord) => $request->validated());

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($company)->for($employee)->create(['attendance_date' => '2026-05-09']);
    AttendanceRecord::factory()->for($company)->for($employee)->create(['attendance_date' => '2026-05-10']);

    $this->actingAs($user)
        ->patchJson("/attendance-validation-update/{$attendanceRecord->id}", [
            'attendance_date' => '2026-05-10',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['attendance_date']);
});

test('manual clock attendance request validates tenant employee and clock payload', function () {
    Route::post('/attendance-validation-manual-clock', fn (ManualClockAttendanceRequest $request) => $request->validated());

    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();

    $this->actingAs($user)
        ->postJson('/attendance-validation-manual-clock', [
            'employee_id' => $otherEmployee->id,
            'attendance_date' => '2026-05-09',
            'clock_action' => 'pause',
            'clock_at' => 'not-date',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['employee_id', 'clock_action', 'clock_at']);

    $this->actingAs($user)
        ->postJson('/attendance-validation-manual-clock', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-09',
            'clock_action' => 'clock_in',
            'clock_at' => '2026-05-09 08:00:00',
            'source' => AttendanceSource::Web->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('employee_id', $employee->id);
});
