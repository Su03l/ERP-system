<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiryNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionExpiryService
{
    public function __construct(private readonly SubscriptionLifecycleService $lifecycleService) {}

    /**
     * @return array{grace_started: int, expired: int, notified: int}
     */
    public function process(?int $companyId = null, ?Carbon $now = null): array
    {
        $now ??= now();

        $summary = [
            'grace_started' => 0,
            'expired' => 0,
            'notified' => 0,
        ];

        $this->expiredTrials($companyId, $now)->each(function (CompanySubscription $subscription) use (&$summary, $now): void {
            $summary[$this->moveExpiredSubscriptionForward($subscription, $now)]++;
            $summary['notified'] += $this->notifyCompanyAdmins($subscription->refresh(), 'trial_expired');
        });

        $this->expiredSubscriptions($companyId, $now)->each(function (CompanySubscription $subscription) use (&$summary, $now): void {
            $summary[$this->moveExpiredSubscriptionForward($subscription, $now)]++;
            $summary['notified'] += $this->notifyCompanyAdmins($subscription->refresh(), 'subscription_expired');
        });

        $this->endedGraceSubscriptions($companyId, $now)->each(function (CompanySubscription $subscription) use (&$summary, $now): void {
            $oldValues = $this->lifecycleService->snapshot($subscription);

            DB::transaction(function () use ($now, $oldValues, $subscription): void {
                $subscription->forceFill([
                    'status' => SubscriptionStatus::Expired,
                    'ends_at' => $now,
                    'grace_ends_at' => null,
                ])->save();

                $this->lifecycleService->audit('subscription.expired_after_grace', $subscription, $oldValues);
            });

            $summary['expired']++;
            $summary['notified'] += $this->notifyCompanyAdmins($subscription->refresh(), 'grace_ended');
        });

        return $summary;
    }

    /**
     * @return Collection<int, CompanySubscription>
     */
    private function expiredTrials(?int $companyId, Carbon $now): Collection
    {
        return CompanySubscription::query()
            ->with('company.users', 'plan')
            ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
            ->where('status', SubscriptionStatus::Trialing->value)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', $now)
            ->get();
    }

    /**
     * @return Collection<int, CompanySubscription>
     */
    private function expiredSubscriptions(?int $companyId, Carbon $now): Collection
    {
        return CompanySubscription::query()
            ->with('company.users', 'plan')
            ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::PastDue->value])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->get();
    }

    /**
     * @return Collection<int, CompanySubscription>
     */
    private function endedGraceSubscriptions(?int $companyId, Carbon $now): Collection
    {
        return CompanySubscription::query()
            ->with('company.users', 'plan')
            ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
            ->where('status', SubscriptionStatus::Grace->value)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<', $now)
            ->get();
    }

    private function moveExpiredSubscriptionForward(CompanySubscription $subscription, Carbon $now): string
    {
        $oldValues = $this->lifecycleService->snapshot($subscription);
        $graceDays = $this->lifecycleService->gracePeriodDays();

        return DB::transaction(function () use ($graceDays, $now, $oldValues, $subscription): string {
            if ($graceDays > 0) {
                $graceEndsAt = $now->copy()->addDays($graceDays);

                $subscription->forceFill([
                    'status' => SubscriptionStatus::Grace,
                    'ends_at' => $graceEndsAt,
                    'grace_ends_at' => $graceEndsAt,
                ])->save();

                $this->lifecycleService->audit('subscription.grace_started', $subscription, $oldValues, [
                    'grace_days' => $graceDays,
                ]);

                return 'grace_started';
            }

            $subscription->forceFill([
                'status' => SubscriptionStatus::Expired,
                'ends_at' => $now,
                'grace_ends_at' => null,
            ])->save();

            $this->lifecycleService->audit('subscription.expired', $subscription, $oldValues);

            return 'expired';
        });
    }

    private function notifyCompanyAdmins(CompanySubscription $subscription, string $event): int
    {
        $company = $subscription->company;

        if (! $company instanceof Company) {
            return 0;
        }

        $notified = 0;

        foreach ($this->companyAdminRecipients($company) as $user) {
            if ($this->alreadyNotified($user, $subscription, $event)) {
                continue;
            }

            $user->notify(new SubscriptionExpiryNotification($subscription, $event));
            $notified++;
        }

        return $notified;
    }

    /**
     * @return Collection<int, User>
     */
    private function companyAdminRecipients(Company $company): Collection
    {
        $admins = $company->users()
            ->whereHas('roles.permissions', fn ($query) => $query->where('key', 'company_settings.update'))
            ->get();

        return $admins->isNotEmpty() ? $admins : $company->users()->get();
    }

    private function alreadyNotified(User $user, CompanySubscription $subscription, string $event): bool
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->where('type', SubscriptionExpiryNotification::class)
            ->where('data->event', $event)
            ->where('data->subscription_id', $subscription->id)
            ->where('data->reminder_date', now()->toDateString())
            ->exists();
    }
}
