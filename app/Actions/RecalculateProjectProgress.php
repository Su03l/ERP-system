<?php

namespace App\Actions;

use App\Models\Project;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class RecalculateProjectProgress
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Project $project, ?User $actor = null): Project
    {
        return DB::transaction(function () use ($project, $actor): Project {
            $oldValues = $project->attributesToArray();
            $averageProgress = (int) round((float) $project->tasks()->avg('progress_percentage'));

            $project->forceFill([
                'progress_percentage' => max(0, min(100, $averageProgress)),
            ])->save();

            $this->auditLogger->log('project.progress_recalculated', $project, $oldValues, $project->refresh()->attributesToArray(), user: $actor, company: $project->company_id);

            return $project;
        });
    }
}
