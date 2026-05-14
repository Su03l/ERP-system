<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\CompanySubscription;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Support\Facades\DB;

class RenewSubscription
{
    public function __construct(private readonly SubscriptionLifecycleService $lifecycleService) {}

    public function handle(CompanySubscription $subscription, ?User $actor = null): CompanySubscription
    {
        return DB::transaction(function () use ($actor, $subscription): CompanySubscription {
            $oldValues = $this->lifecycleService->snapshot($subscription);
            $startsAt = $subscription->starts_at ?? now();

            $subscription->forceFill([
                'status' => SubscriptionStatus::Active,
                'starts_at' => $startsAt,
                'ends_at' => $this->lifecycleService->nextEndsAt($subscription),
                'trial_ends_at' => null,
                'cancelled_at' => null,
                'grace_ends_at' => null,
            ])->save();

            $this->lifecycleService->audit('subscription.renewed', $subscription, $oldValues, actor: $actor);

            return $subscription->refresh()->load('company', 'plan');
        });
    }
}
