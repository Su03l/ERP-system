<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexProjectTimeLogRequest extends FormRequest
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
            'project_task_id' => ['nullable', 'integer'],
            'employee_id' => ['nullable', 'integer'],
            'is_billable' => ['nullable', 'boolean'],
            'logged_from' => ['nullable', 'date'],
            'logged_until' => ['nullable', 'date', 'after_or_equal:logged_from'],
        ];
    }
}
