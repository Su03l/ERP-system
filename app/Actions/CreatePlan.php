<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreatePlan
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): Plan
    {
        return DB::transaction(function () use ($actor, $data): Plan {
            $plan = Plan::create($data);

            $this->auditLogger->log(
                action: 'plan.created',
                auditable: $plan,
                newValues: $plan->attributesToArray(),
                user: $actor,
            );

            return $plan->refresh();
        });
    }
}
