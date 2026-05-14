<?php

namespace App\Actions;

use App\Enums\SubscriptionStatus;
use App\Models\CompanySubscription;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Support\Facades\DB;

class CancelSubscription
{
    public function __construct(private readonly SubscriptionLifecycleService $lifecycleService) {}

    public function handle(CompanySubscription $subscription, ?User $actor = null): CompanySubscription
    {
        return DB::transaction(function () use ($actor, $subscription): CompanySubscription {
            $oldValues = $this->lifecycleService->snapshot($subscription);
            $cancelledAt = now();
            $graceDays = $this->lifecycleService->gracePeriodDays();
            $graceEndsAt = $graceDays > 0 ? $cancelledAt->copy()->addDays($graceDays) : null;

            $subscription->forceFill([
                'status' => $graceEndsAt === null ? SubscriptionStatus::Cancelled : SubscriptionStatus::Grace,
                'cancelled_at' => $cancelledAt,
                'grace_ends_at' => $graceEndsAt,
                'ends_at' => $graceEndsAt ?? $cancelledAt,
            ])->save();

            $this->lifecycleService->audit('subscription.cancelled', $subscription, $oldValues, [
                'grace_period_days' => $graceDays,
            ], $actor);

            return $subscription->refresh()->load('company', 'plan');
        });
    }
}
