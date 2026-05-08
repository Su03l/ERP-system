<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanySettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('company.settings.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'locale' => ['sometimes', 'required', 'string', 'max:10'],
            'timezone' => ['sometimes', 'required', 'timezone'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'date_preference' => ['sometimes', 'required', Rule::in(['gregorian', 'hijri'])],
            'working_days' => ['sometimes', 'array'],
            'working_days.*' => ['string', Rule::in(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])],
            'branding' => ['sometimes', 'array'],
            'branding.logo_path' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'branding.primary_color' => ['sometimes', 'nullable', 'string', 'max:20'],
            'branding.secondary_color' => ['sometimes', 'nullable', 'string', 'max:20'],
            'notification_preferences' => ['sometimes', 'array'],
            'notification_preferences.email_enabled' => ['sometimes', 'boolean'],
            'notification_preferences.database_enabled' => ['sometimes', 'boolean'],
            'notification_preferences.sms_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
