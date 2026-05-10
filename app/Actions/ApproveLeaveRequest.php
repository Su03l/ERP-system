<?php

namespace App\Actions;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LeaveBalanceService;
use App\Services\WorkflowExecutionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ApproveLeaveRequest
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly LeaveBalanceService $leaveBalanceService,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    public function handle(LeaveRequest $leaveRequest, ?User $actor = null, ?string $comment = null): LeaveRequest
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to approve leave requests.');
        }

        Gate::forUser($actor)->authorize('approve', $leaveRequest);

        return DB::transaction(function () use ($actor, $comment, $leaveRequest): LeaveRequest {
            $oldValues = $leaveRequest->attributesToArray();
            $leaveRequest->loadMissing('workflowInstance');

            if ($leaveRequest->workflowInstance !== null && $leaveRequest->workflowInstance->status === 'pending') {
                $this->workflowExecutionService->approve($leaveRequest->workflowInstance, $actor, $comment);
                $leaveRequest->workflowInstance->refresh();
            }

            if ($leaveRequest->workflowInstance === null || $leaveRequest->workflowInstance->status === 'completed') {
                $leaveRequest->status = LeaveRequestStatus::Approved;
                $leaveRequest->approved_by = $actor->id;
                $leaveRequest->approved_at = now();
                $leaveRequest->save();
                $this->leaveBalanceService->deductOnApproval($leaveRequest, $actor);
            } else {
                $leaveRequest->status = LeaveRequestStatus::Pending;
                $leaveRequest->save();
            }
            $this->auditLogger->log('leave_request.approved', $leaveRequest, $oldValues, $leaveRequest->refresh()->attributesToArray(), ['comment' => $comment], $actor, $leaveRequest->company_id);

            return $leaveRequest;
        });
    }
}
