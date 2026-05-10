<?php

namespace App\Actions;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\WorkflowExecutionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RejectLeaveRequest
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    public function handle(LeaveRequest $leaveRequest, ?User $actor = null, ?string $reason = null): LeaveRequest
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to reject leave requests.');
        }
        Gate::forUser($actor)->authorize('reject', $leaveRequest);

        return DB::transaction(function () use ($actor, $leaveRequest, $reason): LeaveRequest {
            $oldValues = $leaveRequest->attributesToArray();
            $leaveRequest->loadMissing('workflowInstance');
            if ($leaveRequest->workflowInstance !== null && $leaveRequest->workflowInstance->status === 'pending') {
                $this->workflowExecutionService->reject($leaveRequest->workflowInstance, $actor, $reason);
            }
            $leaveRequest->status = LeaveRequestStatus::Rejected;
            $leaveRequest->rejected_reason = $reason;
            $leaveRequest->save();
            $this->auditLogger->log('leave_request.rejected', $leaveRequest, $oldValues, $leaveRequest->refresh()->attributesToArray(), ['reason' => $reason], $actor, $leaveRequest->company_id);

            return $leaveRequest;
        });
    }
}
