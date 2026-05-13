<?php

namespace App\Http\Requests;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\CompanyDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->routeCompanyDocument();

        return $document !== null && ($this->user()?->can('update', $document) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['prohibited'],
            'document_type' => ['sometimes', 'required', Rule::enum(DocumentType::class)],
            'title_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'file_path' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'issue_date' => ['sometimes', 'nullable', 'date'],
            'expiry_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'required', Rule::enum(DocumentStatus::class)],
            'notes_ar' => ['sometimes', 'nullable', 'string'],
            'notes_en' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function routeCompanyDocument(): ?CompanyDocument
    {
        $document = $this->route('company_document') ?? $this->route('companyDocument');

        if ($document instanceof CompanyDocument) {
            return $document;
        }

        return $document === null ? null : CompanyDocument::query()->find($document);
    }
}
