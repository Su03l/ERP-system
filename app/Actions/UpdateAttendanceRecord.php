<?php

namespace App\Actions;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceCalculator;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateAttendanceRecord
{
    public function __construct(
        private readonly AttendanceCalculator $attendanceCalculator,
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(AttendanceRecord $attendanceRecord, array $data, ?User $actor = null): AttendanceRecord
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to update attendance records.');
        }

        $this->ensureRecordBelongsToCurrentCompany($attendanceRecord);

        $companyId = $attendanceRecord->company_id;
        $employeeId = (int) ($data['employee_id'] ?? $attendanceRecord->employee_id);
        $attendanceDate = (string) ($data['attendance_date'] ?? $attendanceRecord->attendance_date?->toDateString());

        $this->ensureEmployeeBelongsToCompany($employeeId, $companyId);
        $this->ensureAttendanceIsUnique($attendanceRecord, $employeeId, $attendanceDate);

        return DB::transaction(function () use ($actor, $attendanceRecord, $data): AttendanceRecord {
            $oldValues = $attendanceRecord->attributesToArray();

            $attendanceRecord->fill($data);
            $this->attendanceCalculator->apply($attendanceRecord);
            $attendanceRecord->save();

            $this->auditLogger->log(
                action: 'attendance.updated',
                auditable: $attendanceRecord,
                oldValues: $oldValues,
                newValues: $attendanceRecord->refresh()->attributesToArray(),
                user: $actor,
                company: $attendanceRecord->company_id,
            );

            return $attendanceRecord;
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureRecordBelongsToCurrentCompany(AttendanceRecord $attendanceRecord): void
    {
        if ($this->tenantContext->companyId() !== $attendanceRecord->company_id) {
            throw new AuthorizationException('Attendance record does not belong to the current company.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureEmployeeBelongsToCompany(int $employeeId, int $companyId): void
    {
        if (! Employee::query()->whereKey($employeeId)->where('company_id', $companyId)->exists()) {
            throw new AuthorizationException('Employee does not belong to the current company.');
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureAttendanceIsUnique(AttendanceRecord $attendanceRecord, int $employeeId, string $attendanceDate): void
    {
        $exists = AttendanceRecord::query()
            ->where('company_id', $attendanceRecord->company_id)
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_date', $attendanceDate)
            ->whereKeyNot($attendanceRecord->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'attendance_date' => __('validation.unique', ['attribute' => __('hr.attendance.fields.attendance_date')]),
            ]);
        }
    }
}
