<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\User;

class CompanySubscriptionAccessService
{
    public function canAccess(?User $user): bool
    {
        if (! config('saas.enforce_subscription_access', true)) {
            return true;
        }

        if ($user === null || $user->company_id === null) {
            return true;
        }

        return $user->company instanceof Company && $this->companyCanAccess($user->company);
    }

    public function companyCanAccess(Company $company): bool
    {
        return $company->subscriptions()
            ->whereIn('status', [
                SubscriptionStatus::Trialing->value,
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Grace->value,
            ])
            ->where('starts_at', '<=', now())
            ->where(function ($query): void {
                $query->where(function ($query): void {
                    $query->where('status', SubscriptionStatus::Trialing->value)
                        ->where(function ($query): void {
                            $query->whereNull('trial_ends_at')->orWhere('trial_ends_at', '>=', now());
                        });
                })->orWhere(function ($query): void {
                    $query->where('status', SubscriptionStatus::Active->value)
                        ->where(function ($query): void {
                            $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                        });
                })->orWhere(function ($query): void {
                    $query->where('status', SubscriptionStatus::Grace->value)
                        ->where(function ($query): void {
                            $query->whereNull('grace_ends_at')->orWhere('grace_ends_at', '>=', now());
                        });
                });
            })
            ->exists();
    }

    public function denialMessage(?CompanySubscription $subscription = null): string
    {
        return __('saas.subscription_access.denied', [
            'status' => $subscription?->status?->label() ?? __('saas.subscription_statuses.expired'),
        ]);
    }
}
