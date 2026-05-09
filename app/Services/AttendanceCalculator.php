<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Company;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class AttendanceCalculator
{
    /**
     * @return array{total_work_minutes: int|null, late_minutes: int, overtime_minutes: int, status: string}
     */
    public function calculate(AttendanceRecord $attendanceRecord): array
    {
        $company = $attendanceRecord->company;
        $attendanceDate = CarbonImmutable::parse($attendanceRecord->attendance_date);
        $clockInAt = $attendanceRecord->clock_in_at;
        $clockOutAt = $attendanceRecord->clock_out_at;
        $workStartAt = $this->timeOnDate($attendanceDate, $this->workStartTime($company));
        $workEndAt = $this->timeOnDate($attendanceDate, $this->workEndTime($company));
        $isWorkingDay = $this->isWorkingDay($company, $attendanceDate);

        $totalWorkMinutes = $this->totalWorkMinutes($clockInAt, $clockOutAt);
        $lateMinutes = $clockInAt instanceof CarbonInterface
            ? (int) max(0, $workStartAt->diffInMinutes($clockInAt, false))
            : 0;
        $overtimeMinutes = $clockOutAt instanceof CarbonInterface
            ? (int) max(0, $workEndAt->diffInMinutes($clockOutAt, false))
            : 0;

        return [
            'total_work_minutes' => $totalWorkMinutes,
            'late_minutes' => $lateMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'status' => $this->status($clockInAt, $isWorkingDay, $lateMinutes),
        ];
    }

    public function apply(AttendanceRecord $attendanceRecord): AttendanceRecord
    {
        $attendanceRecord->fill($this->calculate($attendanceRecord));

        return $attendanceRecord;
    }

    private function totalWorkMinutes(?CarbonInterface $clockInAt, ?CarbonInterface $clockOutAt): ?int
    {
        if (! $clockInAt instanceof CarbonInterface || ! $clockOutAt instanceof CarbonInterface) {
            return null;
        }

        return (int) max(0, $clockInAt->diffInMinutes($clockOutAt, false));
    }

    private function status(?CarbonInterface $clockInAt, bool $isWorkingDay, int $lateMinutes): string
    {
        if (! $isWorkingDay && ! $clockInAt instanceof CarbonInterface) {
            return AttendanceStatus::Holiday->value;
        }

        if (! $clockInAt instanceof CarbonInterface) {
            return AttendanceStatus::Absent->value;
        }

        if ($lateMinutes > 0) {
            return AttendanceStatus::Late->value;
        }

        return AttendanceStatus::Present->value;
    }

    private function workStartTime(Company $company): string
    {
        return (string) data_get($company->settings, 'attendance.work_start_time', data_get($company->settings, 'work_start_time', '09:00'));
    }

    private function workEndTime(Company $company): string
    {
        return (string) data_get($company->settings, 'attendance.work_end_time', data_get($company->settings, 'work_end_time', '17:00'));
    }

    /**
     * @return array<int, string>
     */
    private function workingDays(Company $company): array
    {
        $workingDays = data_get($company->settings, 'attendance.working_days', data_get($company->settings, 'working_days'));

        if (! is_array($workingDays) || $workingDays === []) {
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        }

        return array_map(
            fn (mixed $day): string => strtolower((string) $day),
            $workingDays,
        );
    }

    private function isWorkingDay(Company $company, CarbonInterface $attendanceDate): bool
    {
        return in_array(strtolower($attendanceDate->englishDayOfWeek), $this->workingDays($company), true);
    }

    private function timeOnDate(CarbonInterface $date, string $time): CarbonImmutable
    {
        [$hour, $minute] = array_pad(explode(':', $time), 2, 0);

        return CarbonImmutable::parse($date)->setTime((int) $hour, (int) $minute);
    }
}
