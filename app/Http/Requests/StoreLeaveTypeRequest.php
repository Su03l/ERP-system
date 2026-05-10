<?php

namespace App\Http\Requests;

use App\Enums\LeaveTypeStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveTypeRequest extends FormRequest
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
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('leave_types', 'code')->where('company_id', $companyId),
            ],
            'default_days_per_year' => ['nullable', 'numeric', 'min:0', 'max:366'],
            'is_paid' => ['sometimes', 'boolean'],
            'requires_approval' => ['sometimes', 'boolean'],
            'allow_negative_balance' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::enum(LeaveTypeStatus::class)],
            'description' => ['nullable', 'string'],
        ];
    }
}
