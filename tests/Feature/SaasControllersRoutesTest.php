<?php

use App\Enums\AddOnStatus;
use App\Enums\BillingCycle;
use App\Enums\CompanyAddOnStatus;
use App\Enums\PlanStatus;
use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\AddOn;
use App\Models\Company;
use App\Models\CompanyAddOn;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('manages SaaS plans through protected platform endpoints', function () {
    $platformUser = User::factory()->create();
    $tenantUser = User::factory()->for(Company::factory())->create();
    Plan::factory()->create(['status' => PlanStatus::Inactive, 'code' => 'PLAN-OLD']);

    $this->actingAs($tenantUser)
        ->getJson(route('plans.index'))
        ->assertForbidden();

    $planId = $this->actingAs($platformUser)
        ->postJson(route('plans.store'), [
            'name_ar' => 'Starter',
            'name_en' => 'Starter',
            'code' => 'PLAN-STARTER',
            'price_monthly' => '49.00',
            'price_yearly' => '490.00',
            'currency' => 'SAR',
            'trial_days' => 14,
            'status' => PlanStatus::Active->value,
            'limits' => ['users' => 5],
            'features' => ['enabled_modules' => ['hr']],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.code', 'PLAN-STARTER')
        ->json('data.id');

    $this->actingAs($platformUser)
        ->getJson(route('plans.index', ['status' => PlanStatus::Active->value, 'search' => 'STARTER']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $planId)
        ->assertJsonCount(1, 'data');

    $this->actingAs($platformUser)
        ->patchJson(route('plans.update', $planId), ['price_monthly' => '59.00'])
        ->assertSuccessful()
        ->assertJsonPath('data.price_monthly', '59.00');

    $this->actingAs($platformUser)
        ->deleteJson(route('plans.destroy', $planId))
        ->assertNoContent();

    expect(Plan::query()->find($planId)->status)->toBe(PlanStatus::Archived);
});

it('manages subscriptions and SaaS billing invoices without tenant invoice mixing', function () {
    $platformUser = User::factory()->create();
    $company = Company::factory()->create();
    $plan = Plan::factory()->create([
        'price_monthly' => '100.00',
        'price_yearly' => '1000.00',
        'currency' => 'SAR',
    ]);

    $subscriptionId = $this->actingAs($platformUser)
        ->postJson(route('company-subscriptions.store'), [
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active->value,
            'billing_cycle' => BillingCycle::Monthly->value,
            'starts_at' => '2026-05-14',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id)
        ->json('data.id');

    $this->actingAs($platformUser)
        ->getJson(route('company-subscriptions.index', [
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'billing_cycle' => BillingCycle::Monthly->value,
        ]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $subscriptionId)
        ->assertJsonCount(1, 'data');

    $invoiceId = $this->actingAs($platformUser)
        ->postJson(route('subscription-invoices.store'), [
            'company_id' => $company->id,
            'subscription_id' => $subscriptionId,
            'invoice_number' => 'SAAS-INV-HTTP',
            'invoice_date' => '2026-05-14',
            'due_date' => '2026-05-21',
            'status' => SubscriptionInvoiceStatus::Open->value,
            'tax_amount' => '15.00',
            'discount_amount' => '5.00',
            'paid_amount' => '0.00',
            'currency' => 'SAR',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.invoice_number', 'SAAS-INV-HTTP')
        ->json('data.id');

    $this->actingAs($platformUser)
        ->postJson(route('subscription-invoices.mark-paid', $invoiceId), ['paid_amount' => '110.00'])
        ->assertSuccessful()
        ->assertJsonPath('data.status', SubscriptionInvoiceStatus::Paid->value);

    $this->actingAs($platformUser)
        ->getJson(route('subscription-invoices.index', [
            'company_id' => $company->id,
            'status' => SubscriptionInvoiceStatus::Paid->value,
            'invoice_date_from' => '2026-05-01',
            'invoice_date_until' => '2026-05-31',
        ]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $invoiceId)
        ->assertJsonCount(1, 'data');
});

it('manages add-ons and company add-ons through platform endpoints', function () {
    $platformUser = User::factory()->create();
    $company = Company::factory()->create();

    $addOnId = $this->actingAs($platformUser)
        ->postJson(route('add-ons.store'), [
            'name_ar' => 'Advanced reports',
            'name_en' => 'Advanced reports',
            'code' => 'ADDON-REPORTS-HTTP',
            'category' => 'analytics',
            'price_monthly' => '29.00',
            'price_yearly' => '290.00',
            'status' => AddOnStatus::Active->value,
            'feature_key' => 'analytics',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.code', 'ADDON-REPORTS-HTTP')
        ->json('data.id');

    $companyAddOnId = $this->actingAs($platformUser)
        ->postJson(route('company-add-ons.store'), [
            'company_id' => $company->id,
            'add_on_id' => $addOnId,
            'status' => CompanyAddOnStatus::Active->value,
            'starts_at' => '2026-05-14',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.company_id', $company->id)
        ->json('data.id');

    $this->actingAs($platformUser)
        ->getJson(route('add-ons.index', ['status' => AddOnStatus::Active->value, 'category' => 'analytics']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $addOnId)
        ->assertJsonCount(1, 'data');

    $this->actingAs($platformUser)
        ->getJson(route('company-add-ons.index', ['company_id' => $company->id, 'add_on_id' => $addOnId]))
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $companyAddOnId)
        ->assertJsonCount(1, 'data');

    $this->actingAs($platformUser)
        ->postJson(route('company-add-ons.deactivate', $companyAddOnId))
        ->assertSuccessful()
        ->assertJsonPath('data.status', CompanyAddOnStatus::Inactive->value);

    expect(AddOn::query()->whereKey($addOnId)->exists())->toBeTrue()
        ->and(CompanyAddOn::query()->whereKey($companyAddOnId)->exists())->toBeTrue();
});
