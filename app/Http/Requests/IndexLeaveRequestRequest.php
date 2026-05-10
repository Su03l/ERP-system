<?php

namespace App\Http\Requests;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', LeaveRequest::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'employee_id' => ['sometimes', 'nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'leave_type_id' => ['sometimes', 'nullable', 'integer', Rule::exists('leave_types', 'id')->where('company_id', $companyId)],
            'status' => ['sometimes', 'nullable', Rule::enum(LeaveRequestStatus::class)],
            'date_from' => ['sometimes', 'nullable', 'date'],
            'date_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
