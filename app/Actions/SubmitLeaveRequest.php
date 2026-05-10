<?php

namespace App\Actions;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Workflow;
use App\Services\AuditLogger;
use App\Services\WorkflowExecutionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SubmitLeaveRequest
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowExecutionService $workflowExecutionService,
    ) {}

    public function handle(LeaveRequest $leaveRequest, ?User $actor = null): LeaveRequest
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to submit leave requests.');
        }

        Gate::forUser($actor)->authorize('submit', $leaveRequest);

        return DB::transaction(function () use ($actor, $leaveRequest): LeaveRequest {
            $oldValues = $leaveRequest->attributesToArray();
            $workflow = $this->workflowFor($leaveRequest);

            if ($workflow instanceof Workflow) {
                $instance = $this->workflowExecutionService->start($workflow, $actor, $leaveRequest, ['leave_request_id' => $leaveRequest->id]);
                $leaveRequest->workflow_instance_id = $instance->id;
                $leaveRequest->status = $instance->status === 'completed' ? LeaveRequestStatus::Approved : LeaveRequestStatus::Pending;
            } else {
                $leaveRequest->status = LeaveRequestStatus::Pending;
            }

            $leaveRequest->save();
            $this->auditLogger->log('leave_request.submitted', $leaveRequest, $oldValues, $leaveRequest->refresh()->attributesToArray(), user: $actor, company: $leaveRequest->company_id);

            return $leaveRequest;
        });
    }

    private function workflowFor(LeaveRequest $leaveRequest): ?Workflow
    {
        return Workflow::query()
            ->forCompany($leaveRequest->company_id)
            ->where('module_key', 'leave')
            ->where('status', 'active')
            ->orderBy('id')
            ->first();
    }
}
