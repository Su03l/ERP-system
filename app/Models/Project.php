<?php

namespace App\Models;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'customer_id',
    'project_manager_id',
    'code',
    'name_ar',
    'name_en',
    'description_ar',
    'description_en',
    'start_date',
    'end_date',
    'budget',
    'status',
    'priority',
    'progress_percentage',
    'metadata',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * Get the customer attached to this project.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the employee managing this project.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'project_manager_id');
    }

    /**
     * Get project tasks for this project.
     *
     * @return HasMany<ProjectTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    /**
     * Get time logs for this project.
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
            'end_date' => 'date',
            'budget' => 'decimal:2',
            'status' => ProjectStatus::class,
            'priority' => ProjectPriority::class,
            'progress_percentage' => 'integer',
            'metadata' => 'array',
        ];
    }
}
