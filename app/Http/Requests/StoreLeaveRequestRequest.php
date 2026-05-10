<?php

namespace App\Http\Requests;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeaveRequestRequest extends FormRequest
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
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'leave_type_id' => ['required', 'integer', Rule::exists('leave_types', 'id')->where('company_id', $companyId)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'total_days' => ['nullable', 'numeric', 'min:0.5', 'max:366'],
            'reason' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::enum(LeaveRequestStatus::class)],
            'workflow_instance_id' => ['nullable', 'integer', Rule::exists('workflow_instances', 'id')->where('company_id', $companyId)],
            'approved_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'approved_at' => ['nullable', 'date'],
            'rejected_reason' => ['nullable', 'string'],
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
                if ($validator->errors()->has('employee_id') || $validator->errors()->has('start_date') || $validator->errors()->has('end_date')) {
                    return;
                }

                if ($this->overlappingRequestExists()) {
                    $validator->errors()->add('start_date', __('validation.unique', ['attribute' => __('hr.leave.fields.date_range')]));
                }
            },
        ];
    }

    protected function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    protected function overlappingRequestExists(?LeaveRequest $ignore = null): bool
    {
        return LeaveRequest::query()
            ->where('company_id', $this->companyId())
            ->where('employee_id', $this->integer('employee_id'))
            ->whereNotIn('status', [
                LeaveRequestStatus::Rejected->value,
                LeaveRequestStatus::Cancelled->value,
            ])
            ->when($ignore !== null, fn ($query) => $query->whereKeyNot($ignore->id))
            ->whereDate('start_date', '<=', (string) $this->input('end_date'))
            ->whereDate('end_date', '>=', (string) $this->input('start_date'))
            ->exists();
    }
}
