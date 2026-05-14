<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectTaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(ProjectTaskStatus::class)],
            'priority' => ['nullable', Rule::enum(ProjectPriority::class)],
            'assigned_employee_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:255'],
            'due_from' => ['nullable', 'date'],
            'due_until' => ['nullable', 'date', 'after_or_equal:due_from'],
            'progress_min' => ['nullable', 'integer', 'min:0', 'max:100'],
            'progress_max' => ['nullable', 'integer', 'min:0', 'max:100', 'gte:progress_min'],
        ];
    }
}
