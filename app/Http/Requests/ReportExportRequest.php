<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ReportExportRequest extends ExecuteReportRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reports.export') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'export_format' => ['required', Rule::in(['pdf', 'excel', 'csv'])],
            'queued' => ['sometimes', 'boolean'],
        ];
    }
}
