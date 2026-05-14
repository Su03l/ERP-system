<?php

use App\Enums\SubscriptionStatus;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\User;
use App\Services\CompanySubscriptionAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('allows active trials, active subscriptions, grace periods, and platform users', function () {
    $service = app(CompanySubscriptionAccessService::class);
    $trialCompany = Company::factory()->create();
    $activeCompany = Company::factory()->create();
    $graceCompany = Company::factory()->create();

    CompanySubscription::factory()->for($trialCompany)->create([
        'status' => SubscriptionStatus::Trialing,
        'trial_ends_at' => now()->addDay(),
    ]);
    CompanySubscription::factory()->for($activeCompany)->create([
        'status' => SubscriptionStatus::Active,
        'ends_at' => now()->addDay(),
    ]);
    CompanySubscription::factory()->for($graceCompany)->create([
        'status' => SubscriptionStatus::Grace,
        'grace_ends_at' => now()->addDay(),
    ]);

    expect($service->canAccess(User::factory()->for($trialCompany)->create()))->toBeTrue()
        ->and($service->canAccess(User::factory()->for($activeCompany)->create()))->toBeTrue()
        ->and($service->canAccess(User::factory()->for($graceCompany)->create()))->toBeTrue()
        ->and($service->canAccess(User::factory()->create(['company_id' => null])))->toBeTrue();
});

it('denies tenant access when subscription is expired', function () {
    Route::get('/subscription-guard-test', fn () => ['ok' => true])
        ->middleware(['web', 'auth', 'subscription.active']);

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    CompanySubscription::factory()->for($company)->create([
        'status' => SubscriptionStatus::Expired,
        'ends_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->getJson('/subscription-guard-test')
        ->assertPaymentRequired()
        ->assertJsonStructure(['message']);
});
