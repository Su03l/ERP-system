<?php

use App\Actions\ActivateCompanyAddOn;
use App\Actions\ActivateSubscription;
use App\Actions\CancelSubscription;
use App\Actions\DeactivateCompanyAddOn;
use App\Actions\GenerateSubscriptionInvoice;
use App\Actions\StartTrialSubscription;
use App\Enums\BillingCycle;
use App\Enums\CompanyAddOnStatus;
use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\SaasSetting;
use App\Models\User;
use App\Notifications\SubscriptionExpiryNotification;
use App\Services\CheckCompanyAddOnAccess;
use App\Services\CompanySubscriptionAccessService;
use App\Services\PlanLimitsService;
use App\Services\SubscriptionExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function grantTenantSaasCoveragePermission217(User $user, string $permissionKey): void
{
    $role = Role::factory()->for($user->company)->create();
    $permission = Permission::query()->firstOrCreate(
        ['key' => $permissionKey],
        ['name' => $permissionKey, 'description' => null],
    );

    $role->permissions()->syncWithoutDetaching([$permission->id]);
    $user->roles()->attach($role, ['company_id' => $user->company_id]);
}

test('plans and subscriptions support lifecycle with platform boundaries', function () {
    Carbon::setTestNow('2026-05-14 00:00:00');

    SaasSetting::factory()->create([
        'default_trial_days' => 14,
        'subscription_grace_period_days' => 3,
    ]);
    $company = Company::factory()->create();
    $tenantUser = User::factory()->for($company)->create();
    $platformUser = User::factory()->create(['company_id' => null]);
    $plan = Plan::factory()->create([
        'code' => 'PRO-217',
        'price_monthly' => '100.00',
        'currency' => 'SAR',
    ]);

    expect(Gate::forUser($platformUser)->allows('plans.create'))->toBeTrue();

    grantTenantSaasCoveragePermission217($tenantUser, 'plans.create');

    expect(Gate::forUser($tenantUser)->denies('plans.create'))->toBeTrue();

    $trial = app(StartTrialSubscription::class)->handle($company, $plan, actor: $platformUser);
    $trialEndsAt = $trial->trial_ends_at?->toDateString();
    $active = app(ActivateSubscription::class)->handle($trial, $platformUser);
    $activeStatus = $active->status;
    $cancelled = app(CancelSubscription::class)->handle($active, $platformUser);

    expect($trialEndsAt)->toBe('2026-05-28')
        ->and($activeStatus)->toBe(SubscriptionStatus::Active)
        ->and($cancelled->status)->toBe(SubscriptionStatus::Grace)
        ->and($cancelled->grace_ends_at?->toDateString())->toBe('2026-05-17');

    Carbon::setTestNow();
});

test('plan limits and add-ons enforce configured access without hardcoded plan names', function () {
    $company = Company::factory()->create(['settings' => ['enabled_modules' => []]]);
    $plan = Plan::factory()->create([
        'limits' => ['employees' => 1],
        'features' => [
            'enabled_modules' => ['hr'],
            'marketplace' => true,
        ],
    ]);
    CompanySubscription::factory()->create([
        'company_id' => $company->id,
        'plan_id' => $plan->id,
        'status' => SubscriptionStatus::Active->value,
        'starts_at' => now()->subDay()->toDateTimeString(),
        'ends_at' => now()->addMonth()->toDateTimeString(),
    ]);
    Employee::factory()->for($company)->create();
    $addOn = AddOn::factory()->create(['feature_key' => 'analytics']);

    $limits = app(PlanLimitsService::class);
    $companyAddOn = app(ActivateCompanyAddOn::class)->handle($company, $addOn);

    expect($limits->checkEmployeesLimit($company, 2)->allowed)->toBeFalse()
        ->and($limits->moduleEnabled($company, 'hr')->allowed)->toBeTrue()
        ->and($limits->marketplaceAccess($company)->allowed)->toBeTrue()
        ->and($companyAddOn->status)->toBe(CompanyAddOnStatus::Active)
        ->and(app(CheckCompanyAddOnAccess::class)->handle($company, 'analytics'))->toBeTrue();

    app(DeactivateCompanyAddOn::class)->handle($companyAddOn);

    expect(app(CheckCompanyAddOnAccess::class)->handle($company->refresh(), 'analytics'))->toBeFalse();
});

test('subscription invoices are generated from plan pricing with exact totals', function () {
    Carbon::setTestNow('2026-05-14 00:00:00');
    SaasSetting::factory()->create(['invoice_numbering_prefix' => 'NC-SUB']);
    $company = Company::factory()->create();
    $plan = Plan::factory()->create([
        'price_monthly' => '100.00',
        'price_yearly' => '1000.00',
        'currency' => 'SAR',
    ]);
    $subscription = CompanySubscription::factory()->for($company)->for($plan)->create([
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Yearly,
    ]);

    $invoice = app(GenerateSubscriptionInvoice::class)->handle($subscription, [
        'tax_amount' => '150.00',
        'discount_amount' => '50.00',
        'paid_amount' => '0.00',
    ]);

    expect($invoice->status)->toBe(SubscriptionInvoiceStatus::Open)
        ->and($invoice->subtotal)->toBe('1000.00')
        ->and($invoice->tax_amount)->toBe('150.00')
        ->and($invoice->discount_amount)->toBe('50.00')
        ->and($invoice->total_amount)->toBe('1100.00')
        ->and($invoice->balance_due)->toBe('1100.00');

    Carbon::setTestNow();
});

test('trial expiry notifications and company access guard reflect subscription status', function () {
    Notification::fake();
    SaasSetting::factory()->create(['subscription_grace_period_days' => 2]);

    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $subscription = CompanySubscription::factory()->for($company)->create([
        'status' => SubscriptionStatus::Trialing,
        'trial_ends_at' => now()->subDay(),
    ]);

    $summary = app(SubscriptionExpiryService::class)->process();

    expect($summary['grace_started'])->toBe(1)
        ->and($subscription->refresh()->status)->toBe(SubscriptionStatus::Grace)
        ->and(app(CompanySubscriptionAccessService::class)->canAccess($user))->toBeTrue();

    Notification::assertSentTo($user, SubscriptionExpiryNotification::class);

    $subscription->forceFill([
        'status' => SubscriptionStatus::Expired,
        'grace_ends_at' => null,
    ])->save();

    expect(app(CompanySubscriptionAccessService::class)->canAccess($user))->toBeFalse();
});
