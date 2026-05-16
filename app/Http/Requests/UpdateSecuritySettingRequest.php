<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSecuritySettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $companyId = $this->user()?->company_id;

        return $companyId !== null && ($this->user()?->hasPermission('security_settings.update', $companyId) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_timeout_minutes' => ['sometimes', 'integer', 'min:5', 'max:1440'],
            'password_policy' => ['sometimes', 'nullable', 'array'],
            'two_factor_authentication_enabled' => ['sometimes', 'boolean'],
            'allowed_login_ips' => ['sometimes', 'nullable', 'array'],
            'allowed_login_ips.*' => ['ip'],
            'audit_retention_days' => ['sometimes', 'integer', 'min:30', 'max:3650'],
            'export_approval_required' => ['sometimes', 'boolean'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
