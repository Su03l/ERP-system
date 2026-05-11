<?php

namespace App\Http\Requests;

use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePayrollPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        $period = $this->routePayrollPeriod();

        return $period !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $period->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'starts_on' => ['sometimes', 'required', 'date'],
            'ends_on' => ['sometimes', 'required', 'date', 'after_or_equal:starts_on'],
            'pay_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::enum(PayrollPeriodStatus::class)],
            'closed_at' => ['sometimes', 'nullable', 'date'],
            'closed_by' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
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
                $period = $this->routePayrollPeriod();

                if ($period === null || $validator->errors()->has('starts_on') || $validator->errors()->has('ends_on')) {
                    return;
                }

                if ($this->overlappingPeriodExists($period)) {
                    $validator->errors()->add('starts_on', __('validation.custom.payroll_periods.overlap'));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routePayrollPeriod(): ?PayrollPeriod
    {
        $period = $this->route('payroll_period') ?? $this->route('payrollPeriod');

        if ($period instanceof PayrollPeriod) {
            return $period;
        }

        return $period === null ? null : PayrollPeriod::query()->find($period);
    }

    private function overlappingPeriodExists(PayrollPeriod $ignore): bool
    {
        $startsOn = (string) ($this->input('starts_on') ?? $ignore->starts_on?->toDateString());
        $endsOn = (string) ($this->input('ends_on') ?? $ignore->ends_on?->toDateString());

        return PayrollPeriod::query()
            ->where('company_id', $this->companyId())
            ->whereKeyNot($ignore->id)
            ->overlapping($startsOn, $endsOn)
            ->exists();
    }
}
