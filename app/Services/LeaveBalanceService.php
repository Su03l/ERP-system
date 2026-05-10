<?php

namespace App\Services;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveBalanceService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function calculateTotalDays(CarbonInterface|string $startDate, CarbonInterface|string $endDate): float
    {
        $start = is_string($startDate) ? CarbonImmutable::parse($startDate) : CarbonImmutable::instance($startDate);
        $end = is_string($endDate) ? CarbonImmutable::parse($endDate) : CarbonImmutable::instance($endDate);

        if ($end->lt($start)) {
            return 0.0;
        }

        return (float) ($start->startOfDay()->diffInDays($end->startOfDay()) + 1);
    }

    public function hasSufficientBalance(LeaveRequest $leaveRequest): bool
    {
        $balance = $this->balanceFor($leaveRequest);
        $remainingDays = (float) $balance->remaining_days;

        return $leaveRequest->leaveType?->allow_negative_balance === true
            || $remainingDays >= (float) $leaveRequest->total_days;
    }

    /**
     * @throws ValidationException
     */
    public function deductOnApproval(LeaveRequest $leaveRequest, ?User $actor = null): LeaveBalance
    {
        $actor ??= Auth::user();

        return DB::transaction(function () use ($actor, $leaveRequest): LeaveBalance {
            if ($leaveRequest->status !== LeaveRequestStatus::Approved) {
                throw ValidationException::withMessages([
                    'leave_request' => __('validation.leave_request_must_be_approved'),
                ]);
            }

            $balance = $this->lockedBalanceFor($leaveRequest);
            $oldValues = $balance->attributesToArray();
            $totalDays = (float) $leaveRequest->total_days;

            if ($leaveRequest->leaveType?->allow_negative_balance !== true && (float) $balance->remaining_days < $totalDays) {
                throw ValidationException::withMessages([
                    'leave_balance' => __('validation.leave_balance_insufficient'),
                ]);
            }

            $balance->used_days = (float) $balance->used_days + $totalDays;
            $balance->remaining_days = (float) $balance->remaining_days - $totalDays;
            $balance->save();

            $this->auditLogger->log(
                action: 'leave_balance.deducted',
                auditable: $balance,
                oldValues: $oldValues,
                newValues: $balance->refresh()->attributesToArray(),
                metadata: ['leave_request_id' => $leaveRequest->id],
                user: $actor,
                company: $leaveRequest->company_id,
            );

            return $balance;
        });
    }

    public function restoreOnCancellation(LeaveRequest $leaveRequest, ?User $actor = null): LeaveBalance
    {
        $actor ??= Auth::user();

        return DB::transaction(function () use ($actor, $leaveRequest): LeaveBalance {
            $balance = $this->lockedBalanceFor($leaveRequest);
            $oldValues = $balance->attributesToArray();
            $totalDays = (float) $leaveRequest->total_days;

            $balance->used_days = max(0, (float) $balance->used_days - $totalDays);
            $balance->remaining_days = (float) $balance->remaining_days + $totalDays;
            $balance->save();

            $this->auditLogger->log(
                action: 'leave_balance.restored',
                auditable: $balance,
                oldValues: $oldValues,
                newValues: $balance->refresh()->attributesToArray(),
                metadata: ['leave_request_id' => $leaveRequest->id],
                user: $actor,
                company: $leaveRequest->company_id,
            );

            return $balance;
        });
    }

    private function balanceFor(LeaveRequest $leaveRequest): LeaveBalance
    {
        return LeaveBalance::query()->firstOrCreate([
            'company_id' => $leaveRequest->company_id,
            'employee_id' => $leaveRequest->employee_id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'year' => (int) $leaveRequest->start_date->year,
        ], [
            'opening_balance' => 0,
            'accrued_days' => 0,
            'used_days' => 0,
            'remaining_days' => 0,
            'metadata' => [],
        ]);
    }

    private function lockedBalanceFor(LeaveRequest $leaveRequest): LeaveBalance
    {
        $this->balanceFor($leaveRequest);

        return LeaveBalance::query()
            ->where('company_id', $leaveRequest->company_id)
            ->where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', (int) $leaveRequest->start_date->year)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
