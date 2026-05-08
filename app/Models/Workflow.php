<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\WorkflowFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['company_id', 'name', 'module_key', 'trigger_type', 'status', 'conditions'])]
class Workflow extends Model
{
    /** @use HasFactory<WorkflowFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return HasMany<WorkflowStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('order');
    }

    /**
     * @return HasMany<WorkflowInstance, $this>
     */
    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conditions' => 'array',
        ];
    }
}
