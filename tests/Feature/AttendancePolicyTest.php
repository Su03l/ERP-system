<?php

use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function grantAttendancePolicyPermissions(User $user, array $permissionKeys): void
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

test('attendance policy protects resource permissions and tenant boundary', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $otherEmployee = Employee::factory()->for($otherCompany)->create();
    $attendanceRecord = AttendanceRecord::factory()->for($company)->for($employee)->create();
    $otherAttendanceRecord = AttendanceRecord::factory()->for($otherCompany)->for($otherEmployee)->create();

    expect($user->can('view', $attendanceRecord))->toBeFalse()
        ->and($user->can('create', AttendanceRecord::class))->toBeFalse();

    grantAttendancePolicyPermissions($user, [
        'attendance.view',
        'attendance.create',
        'attendance.update',
        'attendance.delete',
        'attendance.recalculate',
        'attendance.export',
    ]);

    expect($user->can('view', $attendanceRecord))->toBeTrue()
        ->and($user->can('view', $otherAttendanceRecord))->toBeFalse()
        ->and($user->can('create', AttendanceRecord::class))->toBeTrue()
        ->and($user->can('update', $attendanceRecord))->toBeTrue()
        ->and($user->can('delete', $attendanceRecord))->toBeTrue()
        ->and($user->can('recalculate', $attendanceRecord))->toBeTrue()
        ->and($user->can('export', AttendanceRecord::class))->toBeTrue();
});

test('attendance clock policy allows own employee profile or attendance clock permission', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create(['user_id' => $user->id]);
    $otherEmployee = Employee::factory()->for($company)->create();
    $otherUser = User::factory()->for($company)->create();

    expect($user->can('clock', [AttendanceRecord::class, $employee]))->toBeTrue()
        ->and($user->can('clock', [AttendanceRecord::class, $otherEmployee]))->toBeFalse();

    grantAttendancePolicyPermissions($otherUser, ['attendance.clock']);

    expect($otherUser->can('clock', [AttendanceRecord::class, $otherEmployee]))->toBeTrue();
});
