<?php

namespace App\Actions;

use App\Models\CompanySubscription;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateCompanySubscription
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(CompanySubscription $subscription, array $data, ?User $actor = null): CompanySubscription
    {
        return DB::transaction(function () use ($actor, $data, $subscription): CompanySubscription {
            $oldValues = $subscription->attributesToArray();
            $subscription->update($data);

            $this->auditLogger->log(
                action: 'subscription.updated',
                auditable: $subscription,
                oldValues: $oldValues,
                newValues: $subscription->refresh()->attributesToArray(),
                user: $actor,
                company: $subscription->company_id,
            );

            return $subscription->load(['company', 'plan']);
        });
    }
}
