<?php

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantAttendanceRoutePermissions(User $user, array $permissionKeys): void
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

function attendanceRoutePayload(Employee $employee, array $overrides = []): array
{
    return array_merge([
        'employee_id' => $employee->id,
        'attendance_date' => '2026-05-10',
        'clock_in_at' => '2026-05-10 09:00:00',
        'clock_out_at' => '2026-05-10 17:00:00',
        'status' => AttendanceStatus::Present->value,
        'source' => AttendanceSource::Manual->value,
    ], $overrides);
}

test('attendance index is tenant scoped and supports filters', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create(['department_id' => $department->id]);
    $matchingRecord = AttendanceRecord::factory()->for($company)->for($employee)->create([
        'attendance_date' => '2026-05-10',
        'status' => AttendanceStatus::Late->value,
    ]);

    AttendanceRecord::factory()->for($company)->create(['attendance_date' => '2026-05-11', 'status' => AttendanceStatus::Present->value]);
    AttendanceRecord::factory()->for($otherCompany)->create(['attendance_date' => '2026-05-10', 'status' => AttendanceStatus::Late->value]);
    grantAttendanceRoutePermissions($user, ['attendance.view']);

    $this->actingAs($user)
        ->getJson("/attendance-records?employee_id={$employee->id}&department_id={$department->id}&status=late&date_from=2026-05-01&date_to=2026-05-31")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingRecord->id);
});

test('attendance store endpoint uses form request action and audit logging', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantAttendanceRoutePermissions($user, ['attendance.create']);

    $this->actingAs($user)
        ->postJson('/attendance-records', attendanceRoutePayload($employee))
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id)
        ->assertJsonPath('data.employee_id', $employee->id);

    expect(AttendanceRecord::query()->where('company_id', $company->id)->where('employee_id', $employee->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'attendance.created')->exists())->toBeTrue();
});

test('attendance show and update endpoints use policy authorization', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($company)->for($employee)->create(['notes' => 'Old']);
    grantAttendanceRoutePermissions($user, ['attendance.view', 'attendance.update']);

    $this->actingAs($user)
        ->getJson("/attendance-records/{$attendanceRecord->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $attendanceRecord->id);

    $this->actingAs($user)
        ->patchJson("/attendance-records/{$attendanceRecord->id}", ['notes' => 'Updated'])
        ->assertSuccessful()
        ->assertJsonPath('data.notes', 'Updated');

    expect(AuditLog::query()->where('action', 'attendance.updated')->where('auditable_id', $attendanceRecord->id)->exists())->toBeTrue();
});

test('attendance destroy endpoint deletes and audits records', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($company)->create();
    grantAttendanceRoutePermissions($user, ['attendance.delete']);

    $this->actingAs($user)
        ->deleteJson("/attendance-records/{$attendanceRecord->id}")
        ->assertNoContent();

    expect(AttendanceRecord::query()->whereKey($attendanceRecord->id)->exists())->toBeFalse()
        ->and(AuditLog::query()->where('action', 'attendance.deleted')->where('auditable_id', $attendanceRecord->id)->exists())->toBeTrue();
});

test('attendance clock endpoints use clock action and employee tenant scope', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantAttendanceRoutePermissions($user, ['attendance.clock']);

    $this->actingAs($user)
        ->postJson('/attendance-records/clock-in', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-10',
            'clock_at' => '2026-05-10 09:05:00',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.employee_id', $employee->id)
        ->assertJsonPath('data.clock_in_at', '2026-05-10T09:05:00.000000Z');

    $this->actingAs($user)
        ->postJson('/attendance-records/clock-out', [
            'employee_id' => $employee->id,
            'attendance_date' => '2026-05-10',
            'clock_at' => '2026-05-10 17:10:00',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.employee_id', $employee->id);

    expect(AuditLog::query()->where('action', 'attendance.clocked_in')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('action', 'attendance.clocked_out')->exists())->toBeTrue();
});
