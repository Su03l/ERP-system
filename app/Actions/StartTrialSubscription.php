<?php

namespace App\Actions;

use App\Enums\SubscriptionBillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartTrialSubscription
{
    public function __construct(private readonly SubscriptionLifecycleService $lifecycleService) {}

    /**
     * @param  array{billing_cycle?: SubscriptionBillingCycle|string, starts_at?: Carbon|string|null, trial_days?: int|null, metadata?: array<string, mixed>}  $data
     *
     * @throws ValidationException
     */
    public function handle(Company $company, Plan $plan, array $data = [], ?User $actor = null): CompanySubscription
    {
        return DB::transaction(function () use ($actor, $company, $data, $plan): CompanySubscription {
            $this->lifecycleService->ensureNoOpenSubscription($company);

            $startsAt = isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : now();
            $trialDays = (int) ($data['trial_days'] ?? $plan->trial_days ?? $this->lifecycleService->defaultTrialDays());

            $subscription = CompanySubscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Trialing,
                'billing_cycle' => $this->billingCycle($data['billing_cycle'] ?? SubscriptionBillingCycle::Monthly),
                'starts_at' => $startsAt,
                'ends_at' => null,
                'trial_ends_at' => $trialDays > 0 ? $startsAt->copy()->addDays($trialDays) : $startsAt,
                'cancelled_at' => null,
                'grace_ends_at' => null,
                'metadata' => $data['metadata'] ?? [],
            ]);

            $this->lifecycleService->audit('subscription.trial_started', $subscription, null, [
                'trial_days' => $trialDays,
            ], $actor);

            return $subscription->refresh()->load('company', 'plan');
        });
    }

    private function billingCycle(SubscriptionBillingCycle|string $cycle): SubscriptionBillingCycle
    {
        return $cycle instanceof SubscriptionBillingCycle ? $cycle : SubscriptionBillingCycle::from($cycle);
    }
}
