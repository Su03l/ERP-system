<?php

namespace App\Actions;

use App\Enums\AttendanceSource;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceCalculator;
use App\Services\AuditLogger;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ClockInEmployee
{
    public function __construct(
        private readonly AttendanceCalculator $attendanceCalculator,
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function handle(Employee $employee, ?CarbonInterface $clockInAt = null, ?string $ipAddress = null, ?User $actor = null): AttendanceRecord
    {
        $actor ??= Auth::user();
        $clockInAt ??= now();

        if (! $actor instanceof User) {
            throw new AuthorizationException('An authenticated user is required to clock in.');
        }

        Gate::forUser($actor)->authorize('clock', [AttendanceRecord::class, $employee]);
        $this->ensureEmployeeBelongsToCurrentCompany($employee);

        return DB::transaction(function () use ($actor, $clockInAt, $employee, $ipAddress): AttendanceRecord {
            $attendanceDate = CarbonImmutable::parse($clockInAt)->startOfDay();
            $attendanceRecord = AttendanceRecord::query()
                ->where('company_id', $employee->company_id)
                ->where('employee_id', $employee->id)
                ->get()
                ->first(fn (AttendanceRecord $record): bool => $record->attendance_date?->toDateString() === $attendanceDate->toDateString())
                ?? new AttendanceRecord([
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'attendance_date' => $attendanceDate->toDateString(),
                ]);
            $oldValues = $attendanceRecord->exists ? $attendanceRecord->attributesToArray() : null;

            $attendanceRecord->fill([
                'clock_in_at' => $clockInAt,
                'clock_in_ip' => $ipAddress,
                'source' => $attendanceRecord->source?->value ?? AttendanceSource::Web->value,
            ]);

            $this->attendanceCalculator->apply($attendanceRecord);
            $attendanceRecord->save();

            $this->auditLogger->log(
                action: 'attendance.clocked_in',
                auditable: $attendanceRecord,
                oldValues: $oldValues,
                newValues: $attendanceRecord->refresh()->attributesToArray(),
                user: $actor,
                company: $employee->company_id,
            );

            return $attendanceRecord;
        });
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureEmployeeBelongsToCurrentCompany(Employee $employee): void
    {
        if ($this->tenantContext->companyId() !== $employee->company_id) {
            throw new AuthorizationException('Employee does not belong to the current company.');
        }
    }
}
