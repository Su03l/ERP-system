<?php

namespace App\Http\Requests;

use App\Models\EmployeeDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employeeDocument = $this->employeeDocument();

        return $employeeDocument instanceof EmployeeDocument
            && ($this->user()?->can('update', $employeeDocument) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = (int) ($this->user()?->company_id ?? 0);

        return [
            'company_id' => ['prohibited'],
            'employee_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'document_type' => ['sometimes', 'required', 'string', 'max:100'],
            'title_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'file_path' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'issue_date' => ['sometimes', 'nullable', 'date'],
            'expiry_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'required', 'string', 'max:50'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function employeeDocument(): ?EmployeeDocument
    {
        $employeeDocument = $this->route('employee_document') ?? $this->route('employeeDocument');

        if ($employeeDocument instanceof EmployeeDocument) {
            return $employeeDocument;
        }

        if (is_numeric($employeeDocument)) {
            return EmployeeDocument::query()->find((int) $employeeDocument);
        }

        return null;
    }
}
