<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdatePlan
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(Plan $plan, array $data, ?User $actor = null): Plan
    {
        return DB::transaction(function () use ($actor, $data, $plan): Plan {
            $oldValues = $plan->attributesToArray();
            $plan->update($data);

            $this->auditLogger->log(
                action: 'plan.updated',
                auditable: $plan,
                oldValues: $oldValues,
                newValues: $plan->refresh()->attributesToArray(),
                user: $actor,
            );

            return $plan;
        });
    }
}
