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

class CreateAttendanceRecord
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
    public function handle(array $data, ?User $actor = null): AttendanceRecord
    {
        $actor ??= Auth::user();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to create attendance records.');
        }

        $companyId = $this->currentCompanyId();
        $this->ensureEmployeeBelongsToCompany((int) $data['employee_id'], $companyId);
        $this->ensureAttendanceIsUnique((int) $data['employee_id'], (string) $data['attendance_date'], $companyId);

        return DB::transaction(function () use ($actor, $companyId, $data): AttendanceRecord {
            $attendanceRecord = new AttendanceRecord([
                ...$data,
                'company_id' => $companyId,
            ]);

            $this->attendanceCalculator->apply($attendanceRecord);
            $attendanceRecord->save();

            $this->auditLogger->log(
                action: 'attendance.created',
                auditable: $attendanceRecord,
                newValues: $attendanceRecord->attributesToArray(),
                user: $actor,
                company: $companyId,
            );

            return $attendanceRecord->refresh();
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function currentCompanyId(): int
    {
        $companyId = $this->tenantContext->companyId();

        if ($companyId === null) {
            throw new AuthorizationException('A current company is required for attendance records.');
        }

        return $companyId;
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
    private function ensureAttendanceIsUnique(int $employeeId, string $attendanceDate, int $companyId): void
    {
        $exists = AttendanceRecord::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_date', $attendanceDate)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'attendance_date' => __('validation.unique', ['attribute' => __('hr.attendance.fields.attendance_date')]),
            ]);
        }
    }
}
