<?php

namespace App\Models;

use App\Enums\ProjectPriority;
use App\Enums\ProjectTaskStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ProjectTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'project_id',
    'assigned_employee_id',
    'parent_task_id',
    'task_code',
    'title_ar',
    'title_en',
    'description_ar',
    'description_en',
    'start_date',
    'due_date',
    'completed_at',
    'status',
    'priority',
    'estimated_hours',
    'actual_hours',
    'progress_percentage',
    'workflow_instance_id',
    'metadata',
])]
class ProjectTask extends Model
{
    /** @use HasFactory<ProjectTaskFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the project this task belongs to.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the employee assigned to this task.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    /**
     * Get the parent task.
     *
     * @return BelongsTo<ProjectTask, $this>
     */
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'parent_task_id');
    }

    /**
     * Get child tasks.
     *
     * @return HasMany<ProjectTask, $this>
     */
    public function childTasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'parent_task_id');
    }

    /**
     * Get the workflow instance attached to this task.
     *
     * @return BelongsTo<WorkflowInstance, $this>
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * Get time logs for this task.
     *
     * @return HasMany<ProjectTimeLog, $this>
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(ProjectTimeLog::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'status' => ProjectTaskStatus::class,
            'priority' => ProjectPriority::class,
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'progress_percentage' => 'integer',
            'metadata' => 'array',
        ];
    }
}
