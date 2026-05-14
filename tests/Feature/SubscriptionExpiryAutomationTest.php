<?php

use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\SaasSetting;
use App\Models\User;
use App\Notifications\SubscriptionExpiryNotification;
use App\Services\SubscriptionExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('moves expired trials into grace and notifies company users once per day', function () {
    Notification::fake();

    SaasSetting::factory()->create(['subscription_grace_period_days' => 3]);
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $subscription = CompanySubscription::factory()->for($company)->create([
        'status' => SubscriptionStatus::Trialing,
        'trial_ends_at' => now()->subDay(),
        'ends_at' => null,
        'grace_ends_at' => null,
    ]);

    $summary = app(SubscriptionExpiryService::class)->process();

    $subscription->refresh();

    expect($summary)->toMatchArray(['grace_started' => 1, 'expired' => 0, 'notified' => 1])
        ->and($subscription->status)->toBe(SubscriptionStatus::Grace)
        ->and($subscription->grace_ends_at?->isSameDay(now()->addDays(3)))->toBeTrue();

    Notification::assertSentTo($user, SubscriptionExpiryNotification::class);
});

it('marks subscriptions expired when grace has ended', function () {
    Notification::fake();

    $company = Company::factory()->create();
    User::factory()->for($company)->create();
    $subscription = CompanySubscription::factory()->for($company)->create([
        'status' => SubscriptionStatus::Grace,
        'ends_at' => now()->subDay(),
        'grace_ends_at' => now()->subDay(),
    ]);

    $summary = app(SubscriptionExpiryService::class)->process();

    expect($summary['expired'])->toBe(1)
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::Expired)
        ->and($subscription->grace_ends_at)->toBeNull();
});
