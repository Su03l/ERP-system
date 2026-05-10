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

class ReturnLeaveRequest
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    public function handle(LeaveRequest $leaveRequest, ?User $actor = null, ?string $comment = null): LeaveRequest
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to return leave requests.');
        }
        Gate::forUser($actor)->authorize('return', $leaveRequest);

        return DB::transaction(function () use ($actor, $comment, $leaveRequest): LeaveRequest {
            $oldValues = $leaveRequest->attributesToArray();
            $leaveRequest->loadMissing('workflowInstance');
            if ($leaveRequest->workflowInstance !== null && $leaveRequest->workflowInstance->status === 'pending') {
                $this->workflowExecutionService->returnBack($leaveRequest->workflowInstance, $actor, $comment);
            }
            $leaveRequest->status = LeaveRequestStatus::Returned;
            $leaveRequest->save();
            $this->auditLogger->log('leave_request.returned', $leaveRequest, $oldValues, $leaveRequest->refresh()->attributesToArray(), ['comment' => $comment], $actor, $leaveRequest->company_id);

            return $leaveRequest;
        });
    }
}
