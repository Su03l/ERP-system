<?php

namespace App\Http\Requests;

use App\Models\ProjectTask;
use App\Models\ProjectTimeLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProjectTimeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        $timeLog = $this->routeTimeLog();

        return $timeLog !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $timeLog->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        return [
            'company_id' => ['prohibited'],
            'project_id' => ['sometimes', 'required', 'integer', Rule::exists('projects', 'id')->where('company_id', $companyId)],
            'project_task_id' => ['sometimes', 'nullable', 'integer', Rule::exists('project_tasks', 'id')->where('company_id', $companyId)],
            'employee_id' => ['sometimes', 'required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'log_date' => ['sometimes', 'required', 'date'],
            'start_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'end_time' => ['sometimes', 'nullable', 'date_format:H:i', 'after:start_time'],
            'total_minutes' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_billable' => ['sometimes', 'boolean'],
            'notes_ar' => ['sometimes', 'nullable', 'string'],
            'notes_en' => ['sometimes', 'nullable', 'string'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $timeLog = $this->routeTimeLog();
                $projectId = $this->integer('project_id') ?: $timeLog?->project_id;
                $taskId = $this->integer('project_task_id') ?: $timeLog?->project_task_id;

                if ($taskId === null || $taskId === 0) {
                    return;
                }

                $task = ProjectTask::query()->find($taskId);

                if ($task !== null && $task->project_id !== $projectId) {
                    $validator->errors()->add('project_task_id', __('validation.exists', ['attribute' => 'project_task_id']));
                }
            },
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeTimeLog(): ?ProjectTimeLog
    {
        $timeLog = $this->route('project_time_log') ?? $this->route('time_log');

        if ($timeLog instanceof ProjectTimeLog) {
            return $timeLog;
        }

        return $timeLog === null ? null : ProjectTimeLog::query()->find($timeLog);
    }
}
