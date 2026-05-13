<?php

namespace App\Http\Requests;

use App\Models\ProjectTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectTimeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->company_id !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'project_task_id' => ['nullable', 'integer', Rule::exists('project_tasks', 'id')->where('company_id', $companyId)],
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'log_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'total_minutes' => ['required', 'integer', 'min:0'],
            'is_billable' => ['nullable', 'boolean'],
            'notes_ar' => ['nullable', 'string'],
            'notes_en' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $taskId = $this->integer('project_task_id');

                if ($taskId === 0) {
                    return;
                }

                $task = ProjectTask::query()->find($taskId);

                if ($task !== null && $task->project_id !== $this->integer('project_id')) {
                    $validator->errors()->add('project_task_id', __('validation.exists', ['attribute' => 'project_task_id']));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }
}
