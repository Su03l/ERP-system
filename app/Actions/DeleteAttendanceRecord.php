<?php

namespace App\Actions;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteAttendanceRecord
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(AttendanceRecord $attendanceRecord, ?User $actor = null): void
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to delete attendance records.');
        }

        Gate::forUser($actor)->authorize('delete', $attendanceRecord);

        if ($this->tenantContext->companyId() !== $attendanceRecord->company_id) {
            throw new AuthorizationException('Attendance record does not belong to the current company.');
        }

        DB::transaction(function () use ($actor, $attendanceRecord): void {
            $oldValues = $attendanceRecord->attributesToArray();
            $companyId = $attendanceRecord->company_id;

            $this->auditLogger->log(
                action: 'attendance.deleted',
                auditable: $attendanceRecord,
                oldValues: $oldValues,
                user: $actor,
                company: $companyId,
            );

            $attendanceRecord->delete();
        });
    }
}
