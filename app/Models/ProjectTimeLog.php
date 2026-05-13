<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ProjectTimeLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'company_id',
    'project_id',
    'project_task_id',
    'employee_id',
    'log_date',
    'start_time',
    'end_time',
    'total_minutes',
    'is_billable',
    'notes_ar',
    'notes_en',
    'metadata',
])]
class ProjectTimeLog extends Model
{
    /** @use HasFactory<ProjectTimeLogFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the project this time log belongs to.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the task this time log belongs to.
     *
     * @return BelongsTo<ProjectTask, $this>
     */
    public function projectTask(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class);
    }

    /**
     * Get the employee who logged the time.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function setTotalMinutesAttribute(int|string $value): void
    {
        if ((int) $value < 0) {
            throw ValidationException::withMessages([
                'total_minutes' => __('validation.min.numeric', ['attribute' => 'total_minutes', 'min' => 0]),
            ]);
        }

        $this->attributes['total_minutes'] = (int) $value;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'total_minutes' => 'integer',
            'is_billable' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
