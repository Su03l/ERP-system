<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDashboardWidgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('dashboard_widgets.manage') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'widget_key' => ['required', 'string', 'max:150'],
            'module' => ['required', 'string', 'max:100'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'resolver' => ['required', 'string', 'max:255'],
            'required_permission' => ['sometimes', 'nullable', 'string', 'max:150'],
            'default_size' => ['sometimes', 'nullable', 'string', 'max:50'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
