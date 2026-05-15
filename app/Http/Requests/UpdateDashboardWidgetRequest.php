<?php

namespace App\Http\Requests;

class UpdateDashboardWidgetRequest extends StoreDashboardWidgetRequest
{
    public function rules(): array
    {
        return [
            'widget_key' => ['sometimes', 'string', 'max:150'],
            'module' => ['sometimes', 'string', 'max:100'],
            'title_ar' => ['sometimes', 'string', 'max:255'],
            'title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'max:50'],
            'resolver' => ['sometimes', 'string', 'max:255'],
            'required_permission' => ['sometimes', 'nullable', 'string', 'max:150'],
            'default_size' => ['sometimes', 'nullable', 'string', 'max:50'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
