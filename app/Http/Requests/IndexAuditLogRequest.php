<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexAuditLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $companyId = $this->user()?->company_id;

        return $companyId !== null && ($this->user()?->hasPermission('audit_logs.view', $companyId) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'integer'],
            'action' => ['sometimes', 'string', 'max:120'],
            'auditable_type' => ['sometimes', 'string', 'max:255'],
            'auditable_id' => ['sometimes', 'integer'],
            'ip_address' => ['sometimes', 'ip'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
        ];
    }
}
