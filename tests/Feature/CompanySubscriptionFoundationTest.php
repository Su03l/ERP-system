<?php

use App\Enums\SubscriptionBillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates the company subscriptions schema', function () {
    expect(Schema::hasColumns('company_subscriptions', [
        'id',
        'company_id',
        'plan_id',
        'status',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'grace_ends_at',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('stores company subscriptions with safe company and plan relationships', function () {
    $company = Company::factory()->create();
    $plan = Plan::factory()->create();

    $subscription = CompanySubscription::factory()->create([
        'company_id' => $company->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => SubscriptionBillingCycle::Yearly,
        'starts_at' => '2026-05-14 00:00:00',
        'ends_at' => '2027-05-14 00:00:00',
        'trial_ends_at' => null,
        'grace_ends_at' => '2027-05-21 00:00:00',
        'metadata' => ['source' => 'manual'],
    ]);

    expect($subscription->company->is($company))->toBeTrue()
        ->and($subscription->plan->is($plan))->toBeTrue()
        ->and($company->subscriptions()->whereKey($subscription)->exists())->toBeTrue()
        ->and($plan->subscriptions()->whereKey($subscription)->exists())->toBeTrue()
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->billing_cycle)->toBe(SubscriptionBillingCycle::Yearly)
        ->and($subscription->starts_at?->toDateString())->toBe('2026-05-14')
        ->and($subscription->metadata)->toBe(['source' => 'manual']);
});

it('scopes company subscriptions to the current company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $subscription = CompanySubscription::factory()->for($company)->create();
    CompanySubscription::factory()->for($otherCompany)->create();

    $this->actingAs($user);

    expect(CompanySubscription::query()->forCurrentCompany()->pluck('id')->all())->toBe([$subscription->id]);
});

it('provides localized subscription enum labels', function () {
    app()->setLocale('ar');

    expect(SubscriptionStatus::Trialing->label())->toBe('تجريبي')
        ->and(SubscriptionBillingCycle::Monthly->label())->toBe('شهري')
        ->and(SubscriptionStatus::values())->toContain('active')
        ->and(SubscriptionBillingCycle::values())->toBe(['monthly', 'yearly']);
});
