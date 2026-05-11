<?php

namespace App\Http\Requests;

use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePayrollPeriodRequest extends FormRequest
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
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'pay_date' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'status' => ['sometimes', Rule::enum(PayrollPeriodStatus::class)],
            'closed_at' => ['nullable', 'date'],
            'closed_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where('company_id', $companyId)],
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
                if ($validator->errors()->has('starts_on') || $validator->errors()->has('ends_on')) {
                    return;
                }

                if ($this->overlappingPeriodExists()) {
                    $validator->errors()->add('starts_on', __('validation.custom.payroll_periods.overlap'));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function overlappingPeriodExists(): bool
    {
        return PayrollPeriod::query()
            ->where('company_id', $this->companyId())
            ->overlapping((string) $this->input('starts_on'), (string) $this->input('ends_on'))
            ->exists();
    }
}
