<?php

namespace App\Http\Requests;

use App\Enums\LeaveTypeStatus;
use App\Models\LeaveType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $leaveType = $this->routeLeaveType();

        return $leaveType !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $leaveType->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $leaveType = $this->routeLeaveType();
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('leave_types', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($leaveType?->id),
            ],
            'default_days_per_year' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:366'],
            'is_paid' => ['sometimes', 'boolean'],
            'requires_approval' => ['sometimes', 'boolean'],
            'allow_negative_balance' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::enum(LeaveTypeStatus::class)],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }

    private function routeLeaveType(): ?LeaveType
    {
        $leaveType = $this->route('leave_type') ?? $this->route('leaveType');

        if ($leaveType instanceof LeaveType) {
            return $leaveType;
        }

        return $leaveType === null ? null : LeaveType::query()->find($leaveType);
    }
}
