<?php

use App\Actions\ClockInEmployee;
use App\Actions\ClockOutEmployee;
use App\Actions\CreateAttendanceRecord;
use App\Actions\RecalculateAttendanceRecord;
use App\Actions\UpdateAttendanceRecord;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantAttendanceActionPermissions(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            [
                'name' => $permissionKey,
                'description' => null,
            ],
        );

        $role->permissions()->attach($permission);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

function attendanceActionPayload(array $overrides = []): array
{
    return array_merge([
        'attendance_date' => '2026-05-11',
        'clock_in_at' => '2026-05-11 09:15:00',
        'clock_out_at' => '2026-05-11 17:30:00',
        'clock_in_ip' => '127.0.0.1',
        'clock_out_ip' => '127.0.0.1',
    ], $overrides);
}

test('create attendance action attaches current company calculates values and audits', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantAttendanceActionPermissions($user, ['attendance.create']);

    $this->actingAs($user);

    $attendanceRecord = app(CreateAttendanceRecord::class)->handle(attendanceActionPayload([
        'employee_id' => $employee->id,
    ]), $user);

    expect($attendanceRecord->company_id)->toBe($company->id)
        ->and($attendanceRecord->status)->toBe(AttendanceStatus::Late)
        ->and($attendanceRecord->late_minutes)->toBe(15)
        ->and($attendanceRecord->overtime_minutes)->toBe(30)
        ->and(AuditLog::query()->where('action', 'attendance.created')->where('auditable_id', $attendanceRecord->id)->exists())->toBeTrue();
});

test('create attendance action rejects duplicates and cross company employees', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    grantAttendanceActionPermissions($user, ['attendance.create']);

    $this->actingAs($user);

    app(CreateAttendanceRecord::class)->handle(attendanceActionPayload(['employee_id' => $employee->id]), $user);

    app(CreateAttendanceRecord::class)->handle(attendanceActionPayload(['employee_id' => $employee->id]), $user);
})->throws(ValidationException::class);

test('update and recalculate attendance actions respect tenant scope and audit changes', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-11',
        'clock_in_at' => '2026-05-11 09:00:00',
        'clock_out_at' => '2026-05-11 17:00:00',
    ]);
    grantAttendanceActionPermissions($user, ['attendance.update', 'attendance.recalculate']);

    $this->actingAs($user);

    $updated = app(UpdateAttendanceRecord::class)->handle($attendanceRecord, [
        'clock_in_at' => '2026-05-11 09:30:00',
        'clock_out_at' => '2026-05-11 18:00:00',
    ], $user);
    $recalculated = app(RecalculateAttendanceRecord::class)->handle($updated, $user);

    expect($updated->late_minutes)->toBe(30)
        ->and($recalculated->total_work_minutes)->toBe(510)
        ->and(AuditLog::query()->where('action', 'attendance.updated')->where('auditable_id', $attendanceRecord->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'attendance.recalculated')->where('auditable_id', $attendanceRecord->id)->exists())->toBeTrue();
});

test('clock in and clock out actions update one tenant scoped attendance record', function () {
    Carbon::setTestNow('2026-05-11 09:00:00');

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantAttendanceActionPermissions($user, ['attendance.clock']);

    $this->actingAs($user);

    $clockedIn = app(ClockInEmployee::class)->handle($employee, now(), '127.0.0.1', $user);

    Carbon::setTestNow('2026-05-11 17:15:00');

    $clockedOut = app(ClockOutEmployee::class)->handle($employee, now(), '127.0.0.2', $user);

    expect($clockedOut->id)->toBe($clockedIn->id)
        ->and($clockedOut->clock_in_ip)->toBe('127.0.0.1')
        ->and($clockedOut->clock_out_ip)->toBe('127.0.0.2')
        ->and($clockedOut->total_work_minutes)->toBe(495)
        ->and(AuditLog::query()->where('action', 'attendance.clocked_in')->where('auditable_id', $clockedIn->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'attendance.clocked_out')->where('auditable_id', $clockedIn->id)->exists())->toBeTrue();

    Carbon::setTestNow();
});

test('attendance actions reject records outside current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($otherCompany)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($otherCompany)->for($employee)->create();
    grantAttendanceActionPermissions($user, ['attendance.update']);

    $this->actingAs($user);

    app(UpdateAttendanceRecord::class)->handle($attendanceRecord, ['notes' => 'Nope'], $user);
})->throws(AuthorizationException::class);
