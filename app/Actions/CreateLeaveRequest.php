<?php

namespace App\Actions;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LeaveBalanceService;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateLeaveRequest
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly LeaveBalanceService $leaveBalanceService,
        private readonly TenantContext $tenantContext,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): LeaveRequest
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create leave requests.');
        }

        Gate::forUser($actor)->authorize('create', LeaveRequest::class);
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            throw new AuthorizationException('A current company is required for leave requests.');
        }

        if (! $actor->hasPermission('leave_requests.create', $companyId) && $actor->employeeProfile?->id !== (int) $data['employee_id']) {
            throw new AuthorizationException('Employees may only create leave requests for themselves.');
        }

        return DB::transaction(function () use ($actor, $companyId, $data): LeaveRequest {
            $leaveRequest = new LeaveRequest([
                ...$data,
                'company_id' => $companyId,
                'status' => $data['status'] ?? LeaveRequestStatus::Draft->value,
                'total_days' => $data['total_days'] ?? $this->leaveBalanceService->calculateTotalDays($data['start_date'], $data['end_date']),
            ]);
            $leaveRequest->save();

            $this->auditLogger->log('leave_request.created', $leaveRequest, newValues: $leaveRequest->attributesToArray(), user: $actor, company: $companyId);

            return $leaveRequest->refresh();
        });
    }
}
