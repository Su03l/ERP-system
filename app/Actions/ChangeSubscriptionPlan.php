<?php

namespace App\Actions;

use App\Enums\SubscriptionBillingCycle;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Support\Facades\DB;

class ChangeSubscriptionPlan
{
    public function __construct(private readonly SubscriptionLifecycleService $lifecycleService) {}

    /**
     * @param  array{billing_cycle?: SubscriptionBillingCycle|string, metadata?: array<string, mixed>}  $data
     */
    public function handle(CompanySubscription $subscription, Plan $plan, array $data = [], ?User $actor = null): CompanySubscription
    {
        return DB::transaction(function () use ($actor, $data, $plan, $subscription): CompanySubscription {
            $oldValues = $this->lifecycleService->snapshot($subscription);

            $metadata = $subscription->metadata ?? [];
            $subscription->forceFill([
                'plan_id' => $plan->id,
                'billing_cycle' => isset($data['billing_cycle']) ? $this->billingCycle($data['billing_cycle']) : $subscription->billing_cycle,
                'metadata' => array_replace($metadata, $data['metadata'] ?? []),
            ])->save();

            $this->lifecycleService->audit('subscription.plan_changed', $subscription, $oldValues, [
                'old_plan_id' => $oldValues['plan_id'] ?? null,
                'new_plan_id' => $plan->id,
            ], $actor);

            return $subscription->refresh()->load('company', 'plan');
        });
    }

    private function billingCycle(SubscriptionBillingCycle|string $cycle): SubscriptionBillingCycle
    {
        return $cycle instanceof SubscriptionBillingCycle ? $cycle : SubscriptionBillingCycle::from($cycle);
    }
}
