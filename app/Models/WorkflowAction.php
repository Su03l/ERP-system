<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\WorkflowActionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'workflow_instance_id',
    'workflow_step_id',
    'acted_by_id',
    'action',
    'comment',
    'metadata',
    'acted_at',
])]
class WorkflowAction extends Model
{
    /** @use HasFactory<WorkflowActionFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return BelongsTo<WorkflowInstance, $this>
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * @return BelongsTo<WorkflowStep, $this>
     */
    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'acted_at' => 'datetime',
        ];
    }
}
