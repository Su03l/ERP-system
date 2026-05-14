<?php

use App\Actions\ActivateSubscription;
use App\Actions\CancelSubscription;
use App\Actions\ChangeSubscriptionPlan;
use App\Actions\ExpireSubscription;
use App\Actions\RenewSubscription;
use App\Actions\StartTrialSubscription;
use App\Enums\SubscriptionBillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\SaasSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('starts trial subscriptions using configured trial days and audits the action', function () {
    Carbon::setTestNow('2026-05-14 10:00:00');
    SaasSetting::factory()->create(['default_trial_days' => 21]);
    $company = Company::factory()->create();
    $plan = Plan::factory()->create(['trial_days' => null]);
    $actor = User::factory()->for($company)->create();

    $subscription = app(StartTrialSubscription::class)->handle($company, $plan, actor: $actor);

    expect($subscription->company->is($company))->toBeTrue()
        ->and($subscription->plan->is($plan))->toBeTrue()
        ->and($subscription->status)->toBe(SubscriptionStatus::Trialing)
        ->and($subscription->billing_cycle)->toBe(SubscriptionBillingCycle::Monthly)
        ->and($subscription->trial_ends_at?->toDateString())->toBe('2026-06-04')
        ->and(AuditLog::query()->where('action', 'subscription.trial_started')->where('auditable_id', $subscription->id)->exists())->toBeTrue();

    Carbon::setTestNow();
});

it('prevents starting a second open subscription for a company', function () {
    $company = Company::factory()->create();
    CompanySubscription::factory()->for($company)->create(['status' => SubscriptionStatus::Active]);

    app(StartTrialSubscription::class)->handle($company, Plan::factory()->create());
})->throws(ValidationException::class);

it('activates changes cancels expires and renews subscriptions safely', function () {
    Carbon::setTestNow('2026-05-14 00:00:00');
    SaasSetting::factory()->create(['subscription_grace_period_days' => 5]);
    $company = Company::factory()->create();
    $firstPlan = Plan::factory()->create();
    $secondPlan = Plan::factory()->create();
    $subscription = CompanySubscription::factory()->for($company)->for($firstPlan)->create([
        'status' => SubscriptionStatus::Trialing,
        'billing_cycle' => SubscriptionBillingCycle::Monthly,
        'starts_at' => now(),
        'trial_ends_at' => now()->addDays(14),
    ]);
    $actor = User::factory()->for($company)->create();

    $activated = app(ActivateSubscription::class)->handle($subscription, $actor);
    expect($activated->status)->toBe(SubscriptionStatus::Active)
        ->and($activated->trial_ends_at)->toBeNull();

    $changed = app(ChangeSubscriptionPlan::class)->handle($activated, $secondPlan, [
        'billing_cycle' => SubscriptionBillingCycle::Yearly,
        'metadata' => ['changed_by' => 'test'],
    ], $actor);
    expect($changed->plan_id)->toBe($secondPlan->id)
        ->and($changed->billing_cycle)->toBe(SubscriptionBillingCycle::Yearly);

    $cancelled = app(CancelSubscription::class)->handle($changed, $actor);
    expect($cancelled->status)->toBe(SubscriptionStatus::Grace)
        ->and($cancelled->grace_ends_at?->toDateString())->toBe('2026-05-19');

    $expired = app(ExpireSubscription::class)->handle($cancelled, $actor);
    expect($expired->status)->toBe(SubscriptionStatus::Expired);

    $renewed = app(RenewSubscription::class)->handle($expired, $actor);

    expect($renewed->status)->toBe(SubscriptionStatus::Active)
        ->and($renewed->ends_at?->toDateString())->toBe('2027-05-14')
        ->and(AuditLog::query()->where('action', 'subscription.activated')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'subscription.plan_changed')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'subscription.cancelled')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'subscription.expired')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'subscription.renewed')->count())->toBe(1);

    Carbon::setTestNow();
});

it('cancels immediately when no grace period is configured', function () {
    Carbon::setTestNow('2026-05-14 00:00:00');
    SaasSetting::factory()->create(['subscription_grace_period_days' => 0]);
    $subscription = CompanySubscription::factory()->create(['status' => SubscriptionStatus::Active]);

    $cancelled = app(CancelSubscription::class)->handle($subscription);

    expect($cancelled->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($cancelled->grace_ends_at)->toBeNull()
        ->and($cancelled->ends_at?->toDateString())->toBe('2026-05-14');

    Carbon::setTestNow();
});
