<?php

use App\Enums\LeaveRequestStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('leave balance service calculates inclusive leave days and checks remaining balance', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create(['allow_negative_balance' => false]);
    $leaveRequest = LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-12',
        'total_days' => 3,
    ]);
    LeaveBalance::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'year' => 2026,
        'remaining_days' => 2,
    ]);

    $service = app(LeaveBalanceService::class);

    expect($service->calculateTotalDays('2026-05-10', '2026-05-12'))->toBe(3.0)
        ->and($service->hasSufficientBalance($leaveRequest))->toBeFalse();
});

test('leave balance service deducts and restores approved leave with audit logs', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create(['allow_negative_balance' => false]);
    $leaveRequest = LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-12',
        'total_days' => 3,
        'status' => LeaveRequestStatus::Approved->value,
    ]);
    $balance = LeaveBalance::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'year' => 2026,
        'used_days' => 1,
        'remaining_days' => 10,
    ]);

    $this->actingAs($user);

    $deducted = app(LeaveBalanceService::class)->deductOnApproval($leaveRequest, $user);

    expect($deducted->id)->toBe($balance->id)
        ->and($deducted->used_days)->toBe('4.00')
        ->and($deducted->remaining_days)->toBe('7.00')
        ->and(AuditLog::query()->where('action', 'leave_balance.deducted')->where('auditable_id', $balance->id)->exists())->toBeTrue();

    $restored = app(LeaveBalanceService::class)->restoreOnCancellation($leaveRequest, $user);

    expect($restored->used_days)->toBe('1.00')
        ->and($restored->remaining_days)->toBe('10.00')
        ->and(AuditLog::query()->where('action', 'leave_balance.restored')->where('auditable_id', $balance->id)->exists())->toBeTrue();
});

test('leave balance service prevents negative balances unless leave type allows it', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $strictLeaveType = LeaveType::factory()->for($company)->create(['allow_negative_balance' => false]);
    $flexibleLeaveType = LeaveType::factory()->for($company)->create(['allow_negative_balance' => true]);
    $strictRequest = LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $strictLeaveType->id,
        'start_date' => '2026-05-10',
        'total_days' => 3,
    ]);
    $flexibleRequest = LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $flexibleLeaveType->id,
        'start_date' => '2026-05-10',
        'total_days' => 3,
    ]);
    LeaveBalance::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $strictLeaveType->id,
        'year' => 2026,
        'remaining_days' => 1,
    ]);
    LeaveBalance::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $flexibleLeaveType->id,
        'year' => 2026,
        'remaining_days' => 1,
    ]);

    app(LeaveBalanceService::class)->deductOnApproval($strictRequest);
})->throws(ValidationException::class);

test('leave balance service allows negative balance when configured', function () {
    $company = Company::factory()->create();
    $employee = Employee::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create(['allow_negative_balance' => true]);
    $leaveRequest = LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => '2026-05-10',
        'total_days' => 3,
    ]);
    LeaveBalance::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'year' => 2026,
        'used_days' => 0,
        'remaining_days' => 1,
    ]);

    $balance = app(LeaveBalanceService::class)->deductOnApproval($leaveRequest);

    expect($balance->used_days)->toBe('3.00')
        ->and($balance->remaining_days)->toBe('-2.00');
});
