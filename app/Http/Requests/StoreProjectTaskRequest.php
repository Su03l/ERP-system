<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectTaskStatus;
use App\Models\ProjectTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectTaskRequest extends FormRequest
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
            'assigned_employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'parent_task_id' => ['nullable', 'integer', Rule::exists('project_tasks', 'id')->where('company_id', $companyId)],
            'task_code' => ['nullable', 'string', 'max:255', Rule::unique('project_tasks', 'task_code')->where('company_id', $companyId)],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'completed_at' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(ProjectTaskStatus::class)],
            'priority' => ['required', Rule::enum(ProjectPriority::class)],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'progress_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'workflow_instance_id' => ['nullable', 'integer', Rule::exists('workflow_instances', 'id')->where('company_id', $companyId)],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $parentTaskId = $this->integer('parent_task_id');

                if ($parentTaskId === 0) {
                    return;
                }

                $parentTask = ProjectTask::query()->find($parentTaskId);

                if ($parentTask !== null && $parentTask->project_id !== $this->integer('project_id')) {
                    $validator->errors()->add('parent_task_id', __('validation.exists', ['attribute' => 'parent_task_id']));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }
}
