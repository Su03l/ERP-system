<?php

namespace App\Http\Requests;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
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
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'attendance_date' => ['required', 'date'],
            'clock_in_at' => ['nullable', 'date'],
            'clock_out_at' => ['nullable', 'date', 'after:clock_in_at'],
            'clock_in_ip' => ['nullable', 'ip', 'max:45'],
            'clock_out_ip' => ['nullable', 'ip', 'max:45'],
            'status' => ['required', Rule::enum(AttendanceStatus::class)],
            'source' => ['nullable', Rule::enum(AttendanceSource::class)],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_minutes' => ['nullable', 'integer', 'min:0'],
            'total_work_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('employee_id') || $validator->errors()->has('attendance_date')) {
                    return;
                }

                $exists = AttendanceRecord::query()
                    ->where('company_id', $this->companyId())
                    ->where('employee_id', $this->integer('employee_id'))
                    ->whereDate('attendance_date', (string) $this->input('attendance_date'))
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
}
