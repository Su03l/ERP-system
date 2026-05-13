<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->routeProject();

        return $project !== null
            && $this->user()?->company_id !== null
            && $this->user()->company_id === $project->company_id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();
        $project = $this->routeProject();

        return [
            'company_id' => ['prohibited'],
            'customer_id' => ['sometimes', 'nullable', 'integer', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'project_manager_id' => ['sometimes', 'nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('projects', 'code')->where('company_id', $companyId)->ignore($project?->id)],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', Rule::enum(ProjectStatus::class)],
            'priority' => ['sometimes', 'required', Rule::enum(ProjectPriority::class)],
            'progress_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }

    private function companyId(): int
    {
        return (int) ($this->user()?->company_id ?? 0);
    }

    private function routeProject(): ?Project
    {
        $project = $this->route('project');

        if ($project instanceof Project) {
            return $project;
        }

        return $project === null ? null : Project::query()->find($project);
    }
}
