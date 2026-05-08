<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\WorkflowInstanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'company_id',
    'workflow_id',
    'current_step_id',
    'requested_by_id',
    'subject_type',
    'subject_id',
    'status',
    'payload',
    'completed_at',
])]
class WorkflowInstance extends Model
{
    /** @use HasFactory<WorkflowInstanceFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return BelongsTo<Workflow, $this>
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * @return BelongsTo<WorkflowStep, $this>
     */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<WorkflowAction, $this>
     */
    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'completed_at' => 'datetime',
        ];
    }
}
