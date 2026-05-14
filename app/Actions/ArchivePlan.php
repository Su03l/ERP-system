<?php

namespace App\Actions;

use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class ArchivePlan
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Plan $plan, ?User $actor = null): Plan
    {
        return DB::transaction(function () use ($actor, $plan): Plan {
            $oldValues = $plan->attributesToArray();
            $plan->forceFill(['status' => PlanStatus::Archived])->save();

            $this->auditLogger->log(
                action: 'plan.archived',
                auditable: $plan,
                oldValues: $oldValues,
                newValues: $plan->refresh()->attributesToArray(),
                user: $actor,
            );

            return $plan;
        });
    }
}
