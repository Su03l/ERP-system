<?php

namespace App\Actions;

use App\Models\CompanySubscription;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class CreateCompanySubscription
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null): CompanySubscription
    {
        return DB::transaction(function () use ($actor, $data): CompanySubscription {
            $subscription = CompanySubscription::create($data);

            $this->auditLogger->log(
                action: 'subscription.created',
                auditable: $subscription,
                newValues: $subscription->attributesToArray(),
                user: $actor,
                company: $subscription->company_id,
            );

            return $subscription->refresh()->load(['company', 'plan']);
        });
    }
}
