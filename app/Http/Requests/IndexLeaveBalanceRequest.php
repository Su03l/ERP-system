<?php

namespace App\Http\Requests;

use App\Models\LeaveBalance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexLeaveBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', LeaveBalance::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'employee_id' => ['sometimes', 'nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'leave_type_id' => ['sometimes', 'nullable', 'integer', Rule::exists('leave_types', 'id')->where('company_id', $companyId)],
            'year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
