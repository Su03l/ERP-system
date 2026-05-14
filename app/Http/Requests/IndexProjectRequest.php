<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ProjectStatus::class)],
            'priority' => ['nullable', Rule::enum(ProjectPriority::class)],
            'customer_id' => ['nullable', 'integer'],
            'project_manager_id' => ['nullable', 'integer'],
            'assigned_employee_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
            'starts_from' => ['nullable', 'date'],
            'starts_until' => ['nullable', 'date', 'after_or_equal:starts_from'],
            'ends_from' => ['nullable', 'date'],
            'ends_until' => ['nullable', 'date', 'after_or_equal:ends_from'],
            'progress_min' => ['nullable', 'integer', 'min:0', 'max:100'],
            'progress_max' => ['nullable', 'integer', 'min:0', 'max:100', 'gte:progress_min'],
        ];
    }
}
