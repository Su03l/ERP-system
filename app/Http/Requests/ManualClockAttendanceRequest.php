<?php

namespace App\Http\Requests;

use App\Enums\AttendanceSource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualClockAttendanceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->routeIs('attendance-records.clock-in')) {
            $this->merge(['clock_action' => 'clock_in']);
        }

        if ($this->routeIs('attendance-records.clock-out')) {
            $this->merge(['clock_action' => 'clock_out']);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'company_id' => ['prohibited'],
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'attendance_date' => ['required', 'date'],
            'clock_action' => ['required', Rule::in(['clock_in', 'clock_out'])],
            'clock_at' => ['required', 'date'],
            'ip_address' => ['nullable', 'ip', 'max:45'],
            'source' => ['nullable', Rule::enum(AttendanceSource::class)],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
