<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\CompanySubscription;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Support\Facades\DB;

class ActivateSubscription
{
    public function __construct(private readonly SubscriptionLifecycleService $lifecycleService) {}

    public function handle(CompanySubscription $subscription, ?User $actor = null): CompanySubscription
    {
        return DB::transaction(function () use ($actor, $subscription): CompanySubscription {
            $oldValues = $this->lifecycleService->snapshot($subscription);

            $subscription->forceFill([
                'status' => SubscriptionStatus::Active,
                'starts_at' => $subscription->starts_at ?? now(),
                'trial_ends_at' => null,
                'cancelled_at' => null,
                'grace_ends_at' => null,
            ])->save();

            $this->lifecycleService->audit('subscription.activated', $subscription, $oldValues, actor: $actor);

            return $subscription->refresh()->load('company', 'plan');
        });
    }
}
