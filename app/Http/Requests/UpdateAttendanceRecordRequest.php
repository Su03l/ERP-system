<?php

namespace App\Http\Requests;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attendanceRecord = $this->attendanceRecord();

        return $attendanceRecord instanceof AttendanceRecord
            && $this->user()?->company_id === $attendanceRecord->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'employee_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'attendance_date' => ['sometimes', 'required', 'date'],
            'clock_in_at' => ['sometimes', 'nullable', 'date'],
            'clock_out_at' => ['sometimes', 'nullable', 'date', 'after:clock_in_at'],
            'clock_in_ip' => ['sometimes', 'nullable', 'ip', 'max:45'],
            'clock_out_ip' => ['sometimes', 'nullable', 'ip', 'max:45'],
            'status' => ['sometimes', 'required', Rule::enum(AttendanceStatus::class)],
            'source' => ['sometimes', 'nullable', Rule::enum(AttendanceSource::class)],
            'late_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'overtime_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'total_work_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $attendanceRecord = $this->attendanceRecord();

                if (! $attendanceRecord instanceof AttendanceRecord || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $employeeId = (int) ($this->input('employee_id', $attendanceRecord->employee_id));
                $attendanceDate = (string) $this->input('attendance_date', $attendanceRecord->attendance_date?->toDateString());

                $exists = AttendanceRecord::query()
                    ->where('company_id', $attendanceRecord->company_id)
                    ->where('employee_id', $employeeId)
                    ->whereDate('attendance_date', $attendanceDate)
                    ->whereKeyNot($attendanceRecord->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('attendance_date', __('validation.unique', ['attribute' => __('hr.attendance.fields.attendance_date')]));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function attendanceRecord(): ?AttendanceRecord
    {
        $attendanceRecord = $this->route('attendance_record') ?? $this->route('attendanceRecord');

        if ($attendanceRecord instanceof AttendanceRecord) {
            return $attendanceRecord;
        }

        if (is_numeric($attendanceRecord)) {
            return AttendanceRecord::query()->find((int) $attendanceRecord);
        }

        return null;
    }
}
