<?php

namespace App\Http\Requests;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\CompanyDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', CompanyDocument::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['prohibited'],
            'document_type' => ['required', Rule::enum(DocumentType::class)],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'file_path' => ['nullable', 'string', 'max:2048'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(DocumentStatus::class)],
            'notes_ar' => ['nullable', 'string'],
            'notes_en' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
