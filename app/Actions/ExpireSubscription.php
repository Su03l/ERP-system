<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\CompanySubscription;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Support\Facades\DB;

class ExpireSubscription
{
    public function __construct(private readonly SubscriptionLifecycleService $lifecycleService) {}

    public function handle(CompanySubscription $subscription, ?User $actor = null): CompanySubscription
    {
        return DB::transaction(function () use ($actor, $subscription): CompanySubscription {
            $oldValues = $this->lifecycleService->snapshot($subscription);
            $expiredAt = now();

            $subscription->forceFill([
                'status' => SubscriptionStatus::Expired,
                'ends_at' => $expiredAt,
                'grace_ends_at' => null,
            ])->save();

            $this->lifecycleService->audit('subscription.expired', $subscription, $oldValues, actor: $actor);

            return $subscription->refresh()->load('company', 'plan');
        });
    }
}
