<?php

namespace App\Http\Requests;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $leaveRequest = $this->routeLeaveRequest();

        return $leaveRequest !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $leaveRequest->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'employee_id' => ['sometimes', 'required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'leave_type_id' => ['sometimes', 'required', 'integer', Rule::exists('leave_types', 'id')->where('company_id', $companyId)],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after_or_equal:start_date'],
            'total_days' => ['sometimes', 'nullable', 'numeric', 'min:0.5', 'max:366'],
            'reason' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::enum(LeaveRequestStatus::class)],
            'workflow_instance_id' => ['sometimes', 'nullable', 'integer', Rule::exists('workflow_instances', 'id')->where('company_id', $companyId)],
            'approved_by' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
            'approved_at' => ['sometimes', 'nullable', 'date'],
            'rejected_reason' => ['sometimes', 'nullable', 'string'],
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
                $leaveRequest = $this->routeLeaveRequest();

                if ($leaveRequest === null || $validator->errors()->has('employee_id') || $validator->errors()->has('start_date') || $validator->errors()->has('end_date')) {
                    return;
                }

                if ($this->overlappingRequestExists($leaveRequest)) {
                    $validator->errors()->add('start_date', __('validation.unique', ['attribute' => __('hr.leave.fields.date_range')]));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeLeaveRequest(): ?LeaveRequest
    {
        $leaveRequest = $this->route('leave_request') ?? $this->route('leaveRequest');

        if ($leaveRequest instanceof LeaveRequest) {
            return $leaveRequest;
        }

        return $leaveRequest === null ? null : LeaveRequest::query()->find($leaveRequest);
    }

    private function overlappingRequestExists(LeaveRequest $ignore): bool
    {
        $employeeId = (int) ($this->input('employee_id') ?? $ignore->employee_id);
        $startDate = (string) ($this->input('start_date') ?? $ignore->start_date?->toDateString());
        $endDate = (string) ($this->input('end_date') ?? $ignore->end_date?->toDateString());

        return LeaveRequest::query()
            ->where('company_id', $this->companyId())
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', [
                LeaveRequestStatus::Rejected->value,
                LeaveRequestStatus::Cancelled->value,
            ])
            ->whereKeyNot($ignore->id)
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->exists();
    }
}
