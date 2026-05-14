<?php

use App\Enums\AddOnStatus;
use App\Enums\BillingCycle;
use App\Enums\CompanyAddOnStatus;
use App\Enums\PlanStatus;
use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Requests\StoreAddOnRequest;
use App\Http\Requests\StoreCompanyAddOnRequest;
use App\Http\Requests\StoreCompanySubscriptionRequest;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\StoreSubscriptionInvoiceRequest;
use App\Http\Requests\UpdateAddOnRequest;
use App\Http\Requests\UpdateCompanyAddOnRequest;
use App\Http\Requests\UpdateCompanySubscriptionRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Requests\UpdateSubscriptionInvoiceRequest;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;
use App\Models\CompanySubscription;
use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Route::post('/test/saas/plans', fn (StorePlanRequest $request) => response()->json($request->validated()));
    Route::patch('/test/saas/plans/{plan}', fn (UpdatePlanRequest $request, Plan $plan) => response()->json($request->validated()));

    Route::post('/test/saas/subscriptions', fn (StoreCompanySubscriptionRequest $request) => response()->json($request->validated()));
    Route::patch('/test/saas/subscriptions/{companySubscription}', fn (UpdateCompanySubscriptionRequest $request, CompanySubscription $companySubscription) => response()->json($request->validated()));

    Route::post('/test/saas/subscription-invoices', fn (StoreSubscriptionInvoiceRequest $request) => response()->json($request->validated()));
    Route::patch('/test/saas/subscription-invoices/{subscriptionInvoice}', fn (UpdateSubscriptionInvoiceRequest $request, SubscriptionInvoice $subscriptionInvoice) => response()->json($request->validated()));

    Route::post('/test/saas/add-ons', fn (StoreAddOnRequest $request) => response()->json($request->validated()));
    Route::patch('/test/saas/add-ons/{addOn}', fn (UpdateAddOnRequest $request, AddOn $addOn) => response()->json($request->validated()));

    Route::post('/test/saas/company-add-ons', fn (StoreCompanyAddOnRequest $request) => response()->json($request->validated()));
    Route::patch('/test/saas/company-add-ons/{companyAddOn}', fn (UpdateCompanyAddOnRequest $request, CompanyAddOn $companyAddOn) => response()->json($request->validated()));
});

it('validates plan payloads for platform users', function () {
    $user = User::factory()->create();
    $existingPlan = Plan::factory()->create(['code' => 'SAAS-PRO']);

    $this->actingAs($user)
        ->postJson('/test/saas/plans', [
            'company_id' => 1,
            'name_ar' => 'Pro',
            'name_en' => 'Pro',
            'code' => $existingPlan->code,
            'price_monthly' => 'invalid',
            'price_yearly' => '499.00',
            'currency' => 'SAR',
            'trial_days' => 14,
            'status' => 'missing',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['company_id', 'code', 'price_monthly', 'status']);

    $this->actingAs($user)
        ->postJson('/test/saas/plans', [
            'name_ar' => 'Pro',
            'name_en' => 'Pro',
            'code' => 'SAAS-STARTER',
            'description_ar' => 'Starter plan',
            'description_en' => 'Starter plan',
            'price_monthly' => '49.99',
            'price_yearly' => '499.00',
            'currency' => 'SAR',
            'trial_days' => 14,
            'status' => PlanStatus::Active->value,
            'limits' => ['users' => 10],
            'features' => ['hr' => true],
            'metadata' => ['source' => 'test'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('code', 'SAAS-STARTER');

    $plan = Plan::factory()->create(['code' => 'SAAS-ENTERPRISE']);

    $this->actingAs($user)
        ->patchJson("/test/saas/plans/{$plan->id}", [
            'code' => $plan->code,
            'name_ar' => 'Enterprise Updated',
            'price_monthly' => '199.99',
            'status' => PlanStatus::Active->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('code', $plan->code);
});

it('validates company subscription payloads against tenant records', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = CompanySubscription::factory()->for($company)->create(['plan_id' => $plan->id]);

    $this->actingAs($user)
        ->postJson('/test/saas/subscriptions', [
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => 'missing',
            'billing_cycle' => 'weekly',
            'starts_at' => now()->toDateString(),
            'trial_ends_at' => now()->subDay()->toDateString(),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status', 'billing_cycle', 'trial_ends_at']);

    $this->actingAs($user)
        ->postJson('/test/saas/subscriptions', [
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Trialing->value,
            'billing_cycle' => BillingCycle::Monthly->value,
            'starts_at' => now()->toDateString(),
            'trial_ends_at' => now()->addDays(14)->toDateString(),
            'metadata' => ['source' => 'manual'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('company_id', $company->id);

    $this->actingAs($user)
        ->patchJson("/test/saas/subscriptions/{$subscription->id}", [
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active->value,
            'billing_cycle' => BillingCycle::Yearly->value,
            'starts_at' => now()->toDateString(),
        ])
        ->assertSuccessful()
        ->assertJsonPath('status', SubscriptionStatus::Active->value);
});

it('validates subscription invoice payloads and invoice uniqueness per company', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $subscription = CompanySubscription::factory()->for($company)->create();
    $otherSubscription = CompanySubscription::factory()->for($otherCompany)->create();
    $invoice = SubscriptionInvoice::factory()->forSubscription($subscription)->create(['invoice_number' => 'SUB-2026-0001']);

    $this->actingAs($user)
        ->postJson('/test/saas/subscription-invoices', [
            'company_id' => $company->id,
            'subscription_id' => $otherSubscription->id,
            'invoice_number' => 'SUB-2026-0001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'open',
            'subtotal' => '100.25',
            'tax_amount' => '15.04',
            'discount_amount' => '5.00',
            'total_amount' => '110.29',
            'paid_amount' => '10.29',
            'balance_due' => '100.00',
            'currency' => 'SAR',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['subscription_id', 'invoice_number']);

    $this->actingAs($user)
        ->postJson('/test/saas/subscription-invoices', [
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'SUB-2026-0002',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'status' => SubscriptionInvoiceStatus::Open->value,
            'subtotal' => '100.25',
            'tax_amount' => '15.04',
            'discount_amount' => '5.00',
            'total_amount' => '110.29',
            'paid_amount' => '10.29',
            'balance_due' => '100.00',
            'currency' => 'SAR',
            'metadata' => ['platform_billing' => true],
        ])
        ->assertSuccessful()
        ->assertJsonPath('invoice_number', 'SUB-2026-0002');

    $this->actingAs($user)
        ->patchJson("/test/saas/subscription-invoices/{$invoice->id}", [
            'invoice_number' => $invoice->invoice_number,
            'status' => SubscriptionInvoiceStatus::Paid->value,
            'paid_amount' => '100.00',
            'balance_due' => '0.00',
        ])
        ->assertSuccessful()
        ->assertJsonPath('invoice_number', $invoice->invoice_number);
});

it('validates add-on payloads for platform users', function () {
    $user = User::factory()->create();
    $existingAddOn = AddOn::factory()->create(['code' => 'ADDON-REPORTS']);

    $this->actingAs($user)
        ->postJson('/test/saas/add-ons', [
            'company_id' => 1,
            'name_ar' => 'Reports',
            'name_en' => 'Reports',
            'code' => $existingAddOn->code,
            'price_monthly' => 'bad',
            'price_yearly' => '199.00',
            'status' => 'missing',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['company_id', 'code', 'price_monthly', 'status']);

    $this->actingAs($user)
        ->postJson('/test/saas/add-ons', [
            'name_ar' => 'Analytics',
            'name_en' => 'Analytics',
            'code' => 'ADDON-ANALYTICS',
            'description_ar' => 'Analytics module',
            'description_en' => 'Analytics module',
            'category' => 'analytics',
            'price_monthly' => '29.99',
            'price_yearly' => '299.99',
            'status' => AddOnStatus::Active->value,
            'feature_key' => 'advanced_analytics',
            'metadata' => ['source' => 'test'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('code', 'ADDON-ANALYTICS');

    $addOn = AddOn::factory()->create(['code' => 'ADDON-EXPORT']);

    $this->actingAs($user)
        ->patchJson("/test/saas/add-ons/{$addOn->id}", [
            'code' => $addOn->code,
            'name_ar' => 'Export Plus',
            'price_monthly' => '39.99',
            'status' => AddOnStatus::Active->value,
        ])
        ->assertSuccessful()
        ->assertJsonPath('code', $addOn->code);
});

it('validates company add-on payloads and company relations', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $addOn = AddOn::factory()->create();
    $companyAddOn = CompanyAddOn::factory()->for($company)->for($addOn)->create();

    $this->actingAs($user)
        ->postJson('/test/saas/company-add-ons', [
            'company_id' => 999999,
            'add_on_id' => $addOn->id,
            'status' => 'missing',
            'starts_at' => now()->toDateString(),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['company_id', 'status']);

    $this->actingAs($user)
        ->postJson('/test/saas/company-add-ons', [
            'company_id' => $company->id,
            'add_on_id' => $addOn->id,
            'status' => CompanyAddOnStatus::Active->value,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'metadata' => ['source' => 'manual'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('company_id', $company->id);

    $this->actingAs($user)
        ->patchJson("/test/saas/company-add-ons/{$companyAddOn->id}", [
            'status' => CompanyAddOnStatus::Inactive->value,
            'starts_at' => now()->toDateString(),
        ])
        ->assertSuccessful()
        ->assertJsonPath('status', CompanyAddOnStatus::Inactive->value);
});
