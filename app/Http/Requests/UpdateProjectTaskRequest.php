<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectTaskStatus;
use App\Models\ProjectTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->routeTask();

        return $task !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $task->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();
        $task = $this->routeTask();

        return [
            'company_id' => ['prohibited'],
            'project_id' => ['sometimes', 'required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'assigned_employee_id' => ['sometimes', 'nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'parent_task_id' => ['sometimes', 'nullable', 'integer', Rule::exists('project_tasks', 'id')->where('company_id', $companyId)],
            'task_code' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('project_tasks', 'task_code')->where('company_id', $companyId)->ignore($task?->id)],
            'title_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'title_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', 'required', Rule::enum(ProjectTaskStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(ProjectPriority::class)],
            'estimated_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'actual_hours' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'progress_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'workflow_instance_id' => ['sometimes', 'nullable', 'integer', Rule::exists('workflow_instances', 'id')->where('company_id', $companyId)],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $task = $this->routeTask();
                $projectId = $this->integer('project_id') ?: $task?->project_id;
                $parentTaskId = $this->integer('parent_task_id');

                if ($task !== null && $parentTaskId === $task->id) {
                    $validator->errors()->add('parent_task_id', __('validation.different', ['attribute' => 'parent_task_id', 'other' => 'project_task']));
                }

                if ($parentTaskId === 0) {
                    return;
                }

                $parentTask = ProjectTask::query()->find($parentTaskId);

                if ($parentTask !== null && $parentTask->project_id !== $projectId) {
                    $validator->errors()->add('parent_task_id', __('validation.exists', ['attribute' => 'parent_task_id']));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeTask(): ?ProjectTask
    {
        $task = $this->route('project_task') ?? $this->route('task');

        if ($task instanceof ProjectTask) {
            return $task;
        }

        return $task === null ? null : ProjectTask::query()->find($task);
    }
}
