<?php

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveTypeStatus;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('leave requests are tenant scoped and expose relationships and casts', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $employee = Employee::factory()->for($company)->create();
    $leaveType = LeaveType::factory()->for($company)->create([
        'status' => LeaveTypeStatus::Active->value,
    ]);
    $workflow = Workflow::factory()->for($company)->create();
    $workflowStep = WorkflowStep::factory()->for($workflow)->create();
    $workflowInstance = WorkflowInstance::factory()->for($company)->create([
        'workflow_id' => $workflow->id,
        'current_step_id' => $workflowStep->id,
        'requested_by_id' => $user->id,
        'subject_type' => LeaveRequest::class,
    ]);
    $approver = User::factory()->for($company)->create();

    $this->actingAs($user);

    $leaveRequest = LeaveRequest::factory()->for($company)->create([
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'workflow_instance_id' => $workflowInstance->id,
        'approved_by' => $approver->id,
        'start_date' => '2026-05-10',
        'end_date' => '2026-05-12',
        'total_days' => 3,
        'status' => LeaveRequestStatus::Approved->value,
        'approved_at' => '2026-05-09 10:00:00',
        'metadata' => ['source' => 'self_service'],
    ]);
    $workflowInstance->update(['subject_id' => $leaveRequest->id]);
    LeaveRequest::factory()->create();

    expect(LeaveRequest::query()->forCurrentCompany()->pluck('id')->all())->toBe([$leaveRequest->id])
        ->and($company->leaveRequests()->whereKey($leaveRequest->id)->exists())->toBeTrue()
        ->and($employee->leaveRequests()->whereKey($leaveRequest->id)->exists())->toBeTrue()
        ->and($leaveType->leaveRequests()->whereKey($leaveRequest->id)->exists())->toBeTrue()
        ->and($workflowInstance->leaveRequests()->whereKey($leaveRequest->id)->exists())->toBeTrue()
        ->and($approver->approvedLeaveRequests()->whereKey($leaveRequest->id)->exists())->toBeTrue()
        ->and($leaveRequest->employee->is($employee))->toBeTrue()
        ->and($leaveRequest->leaveType->is($leaveType))->toBeTrue()
        ->and($leaveRequest->workflowInstance->is($workflowInstance))->toBeTrue()
        ->and($leaveRequest->approvedBy->is($approver))->toBeTrue()
        ->and($leaveRequest->start_date->toDateString())->toBe('2026-05-10')
        ->and($leaveRequest->end_date->toDateString())->toBe('2026-05-12')
        ->and($leaveRequest->total_days)->toBe('3.00')
        ->and($leaveRequest->status)->toBe(LeaveRequestStatus::Approved)
        ->and($leaveRequest->approved_at?->toDateTimeString())->toBe('2026-05-09 10:00:00')
        ->and($leaveRequest->metadata)->toBe(['source' => 'self_service']);
});

test('leave request factory keeps employee and leave type in the same company', function () {
    $leaveRequest = LeaveRequest::factory()->create();

    expect($leaveRequest->employee->company_id)->toBe($leaveRequest->company_id)
        ->and($leaveRequest->leaveType->company_id)->toBe($leaveRequest->company_id)
        ->and($leaveRequest->status)->toBe(LeaveRequestStatus::Draft);
});
