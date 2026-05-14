<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SaasSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SubscriptionLifecycleService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function defaultTrialDays(): int
    {
        return (int) (SaasSetting::query()->latest('id')->value('default_trial_days') ?? 14);
    }

    public function gracePeriodDays(): int
    {
        return (int) (SaasSetting::query()->latest('id')->value('subscription_grace_period_days') ?? 0);
    }

    /**
     * @throws ValidationException
     */
    public function ensureNoOpenSubscription(Company $company): void
    {
        $exists = $company->subscriptions()
            ->whereIn('status', [
                SubscriptionStatus::Trialing->value,
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Grace->value,
                SubscriptionStatus::PastDue->value,
            ])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'company_id' => __('saas.subscriptions.open_subscription_exists'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(CompanySubscription $subscription): array
    {
        return $subscription->attributesToArray();
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function audit(string $action, CompanySubscription $subscription, ?array $oldValues, ?array $metadata = null, ?User $actor = null): void
    {
        $this->auditLogger->log(
            action: $action,
            auditable: $subscription,
            oldValues: $oldValues,
            newValues: $subscription->refresh()->attributesToArray(),
            metadata: $metadata,
            user: $actor,
            company: $subscription->company_id,
        );
    }

    public function nextEndsAt(CompanySubscription $subscription, ?Carbon $from = null): Carbon
    {
        $from ??= $subscription->ends_at instanceof Carbon && $subscription->ends_at->isFuture()
            ? $subscription->ends_at
            : now();

        return $subscription->billing_cycle->value === 'yearly'
            ? $from->copy()->addYear()
            : $from->copy()->addMonth();
    }
}
