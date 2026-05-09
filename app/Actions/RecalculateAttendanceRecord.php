<?php

namespace App\Actions;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\AttendanceCalculator;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecalculateAttendanceRecord
{
    public function __construct(
        private readonly AttendanceCalculator $attendanceCalculator,
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(AttendanceRecord $attendanceRecord, ?User $actor = null): AttendanceRecord
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to recalculate attendance records.');
        }

        if ($this->tenantContext->companyId() !== $attendanceRecord->company_id) {
            throw new AuthorizationException('Attendance record does not belong to the current company.');
        }

        return DB::transaction(function () use ($actor, $attendanceRecord): AttendanceRecord {
            $oldValues = $attendanceRecord->attributesToArray();

            $this->attendanceCalculator->apply($attendanceRecord);
            $attendanceRecord->save();

            $this->auditLogger->log(
                action: 'attendance.recalculated',
                auditable: $attendanceRecord,
                oldValues: $oldValues,
                newValues: $attendanceRecord->refresh()->attributesToArray(),
                user: $actor,
                company: $attendanceRecord->company_id,
            );

            return $attendanceRecord;
        });
    }
}
