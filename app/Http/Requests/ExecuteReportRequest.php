<?php

namespace App\Http\Requests;

use App\Services\ReportRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ExecuteReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'report_key' => ['required', 'string', Rule::in($this->reportKeys())],
            'date_from' => ['sometimes', 'nullable', 'date'],
            'date_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_from'],
            'company_id' => ['sometimes', 'nullable', 'integer'],
            'department_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('departments', 'id')->where('company_id', $companyId),
            ],
            'employee_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'status' => ['sometimes', 'nullable', 'string', 'max:100'],
            'module' => ['sometimes', 'nullable', 'string', 'max:100'],
            'filters' => ['sometimes', 'array'],
            'locale' => ['sometimes', 'nullable', Rule::in(['ar', 'en'])],
            'export_format' => ['sometimes', 'nullable', Rule::in(['pdf', 'excel', 'csv'])],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $userCompanyId = $this->user()?->company_id;
                $requestedCompanyId = $this->integer('company_id') ?: null;

                if ($userCompanyId !== null && $requestedCompanyId !== null && $requestedCompanyId !== (int) $userCompanyId) {
                    $validator->errors()->add('company_id', __('reports.validation.company_scope'));
                }

                $reportKey = $this->string('report_key')->toString();
                $format = $this->string('export_format')->toString();

                if ($reportKey !== '' && $format !== '' && ! ReportRegistry::default()->supportsExport($reportKey, $format)) {
                    $validator->errors()->add('export_format', __('reports.validation.unsupported_export'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'report_key' => __('reports.attributes.report_key'),
            'date_from' => __('reports.attributes.date_from'),
            'date_to' => __('reports.attributes.date_to'),
            'company_id' => __('reports.attributes.company_id'),
            'department_id' => __('reports.attributes.department_id'),
            'employee_id' => __('reports.attributes.employee_id'),
            'export_format' => __('reports.attributes.export_format'),
            'locale' => __('reports.attributes.locale'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function reportKeys(): array
    {
        return collect(ReportRegistry::default()->available())
            ->pluck('key')
            ->all();
    }
}
