<?php

use App\Actions\ApproveLeaveRequest;
use App\Actions\CancelLeaveRequest;
use App\Actions\CreateAttendanceRecord;
use App\Actions\CreateLeaveRequest;
use App\Enums\AttendanceStatus;
use App\Enums\EmployeeStatus;
use App\Enums\LeaveRequestStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function grantHrCoveragePermissions212(User $user, array $permissionKeys): void
{
    $role = Role::factory()->for($user->company)->create();

    foreach ($permissionKeys as $permissionKey) {
        $permission = Permission::query()->firstOrCreate(
            ['key' => $permissionKey],
            ['name' => $permissionKey, 'description' => null],
        );

        $role->permissions()->syncWithoutDetaching([$permission->id]);
    }

    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('employee endpoints enforce tenant scoped relations and salary visibility', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $salaryUser = User::factory()->for($company)->create();
    $department = Department::factory()->for($company)->create();
    $otherDepartment = Department::factory()->for($otherCompany)->create();
    $jobTitle = JobTitle::factory()->for($company)->create();
    $otherJobTitle = JobTitle::factory()->for($otherCompany)->create();

    grantHrCoveragePermissions212($user, ['employees.view', 'employees.create']);
    grantHrCoveragePermissions212($salaryUser, ['employees.view', 'employees.view_salary']);

    $this->actingAs($user)
        ->postJson('/employees', [
            'employee_number' => 'EMP-212-A',
            'first_name_ar' => 'Ali',
            'last_name_ar' => 'Saleh',
            'department_id' => $otherDepartment->id,
            'job_title_id' => $otherJobTitle->id,
            'employment_status' => EmployeeStatus::Active->value,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['department_id', 'job_title_id']);

    $employeeId = $this->actingAs($user)
        ->postJson('/employees', [
            'employee_number' => 'EMP-212-B',
            'first_name_ar' => 'Nora',
            'last_name_ar' => 'Khaled',
            'department_id' => $department->id,
            'job_title_id' => $jobTitle->id,
            'employment_status' => EmployeeStatus::Active->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id)
        ->json('data.id');

    Employee::query()->whereKey($employeeId)->update(['basic_salary' => 9500]);

    $this->actingAs($user)
        ->getJson("/employees/{$employeeId}")
        ->assertSuccessful()
        ->assertJsonMissingPath('data.basic_salary');

    $this->actingAs($salaryUser)
        ->getJson("/employees/{$employeeId}")
        ->assertSuccessful()
        ->assertJsonPath('data.basic_salary', '9500.00');
});

test('attendance creation prevents duplicates and calculates deterministic minutes', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    grantHrCoveragePermissions212($user, ['attendance.create']);

    $this->actingAs($user);

    $record = app(CreateAttendanceRecord::class)->handle([
        'employee_id' => $employee->id,
        'attendance_date' => '2026-05-11',
        'clock_in_at' => '2026-05-11 09:20:00',
        'clock_out_at' => '2026-05-11 18:10:00',
    ], $user);

    expect($record->status)->toBe(AttendanceStatus::Late)
        ->and($record->late_minutes)->toBe(20)
        ->and($record->overtime_minutes)->toBe(70)
        ->and($record->total_work_minutes)->toBe(530)
        ->and(AttendanceRecord::query()->where('company_id', $company->id)->count())->toBe(1);

    app(CreateAttendanceRecord::class)->handle([
        'employee_id' => $employee->id,
        'attendance_date' => '2026-05-11',
    ], $user);
})->throws(ValidationException::class);

test('leave request approval deducts balance and cancellation restores it', function () {
    $company = Company::factory()->create();
    $employeeUser = User::factory()->for($company)->create();
    $approver = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create(['user_id' => $employeeUser->id]);
    $leaveType = LeaveType::factory()->for($company)->create(['allow_negative_balance' => false]);
    $balance = LeaveBalance::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'year' => 2026,
        'used_days' => 1,
        'remaining_days' => 9,
    ]);

    grantHrCoveragePermissions212($approver, [
        'leave_requests.approve',
        'leave_requests.cancel',
    ]);

    $this->actingAs($employeeUser);

    $leaveRequest = app(CreateLeaveRequest::class)->handle([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-12',
        'reason' => 'Family',
        'status' => LeaveRequestStatus::Pending->value,
    ], $employeeUser);

    $approved = app(ApproveLeaveRequest::class)->handle($leaveRequest, $approver);
    $balance->refresh();

    expect($approved->status)->toBe(LeaveRequestStatus::Approved)
        ->and($balance->used_days)->toBe('4.00')
        ->and($balance->remaining_days)->toBe('6.00');

    $cancelled = app(CancelLeaveRequest::class)->handle($approved, $approver);
    $balance->refresh();

    expect($cancelled->status)->toBe(LeaveRequestStatus::Cancelled)
        ->and($balance->used_days)->toBe('1.00')
        ->and($balance->remaining_days)->toBe('9.00');
});
