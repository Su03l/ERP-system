<?php

namespace App\Actions;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LeaveBalanceService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CancelLeaveRequest
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly LeaveBalanceService $leaveBalanceService,
    ) {}

    public function handle(LeaveRequest $leaveRequest, ?User $actor = null): LeaveRequest
    {
        $actor ??= Auth::user();
        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to cancel leave requests.');
        }
        Gate::forUser($actor)->authorize('cancel', $leaveRequest);

        return DB::transaction(function () use ($actor, $leaveRequest): LeaveRequest {
            $oldValues = $leaveRequest->attributesToArray();
            if ($leaveRequest->status === LeaveRequestStatus::Approved) {
                $this->leaveBalanceService->restoreOnCancellation($leaveRequest, $actor);
            }
            $leaveRequest->status = LeaveRequestStatus::Cancelled;
            $leaveRequest->save();
            $this->auditLogger->log('leave_request.cancelled', $leaveRequest, $oldValues, $leaveRequest->refresh()->attributesToArray(), user: $actor, company: $leaveRequest->company_id);

            return $leaveRequest;
        });
    }
}
