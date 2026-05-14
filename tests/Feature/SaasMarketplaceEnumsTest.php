<?php

use App\Enums\AddOnStatus;
use App\Enums\BillingCycle;
use App\Enums\CompanyAddOnStatus;
use App\Enums\PlanStatus;
use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('provides stable SaaS and marketplace enum values', function () {
    expect(PlanStatus::values())->toBe(['active', 'inactive', 'archived'])
        ->and(SubscriptionStatus::values())->toBe(['trialing', 'active', 'past_due', 'grace', 'cancelled', 'expired'])
        ->and(BillingCycle::values())->toBe(['monthly', 'yearly'])
        ->and(SubscriptionInvoiceStatus::values())->toBe(['draft', 'open', 'paid', 'partially_paid', 'overdue', 'cancelled', 'voided'])
        ->and(AddOnStatus::values())->toBe(['active', 'inactive', 'archived'])
        ->and(CompanyAddOnStatus::values())->toBe(['active', 'inactive', 'cancelled', 'expired']);
});

it('provides Arabic labels for SaaS and marketplace enums', function () {
    app()->setLocale('ar');

    expect(PlanStatus::Active->label())->toBe('نشط')
        ->and(SubscriptionStatus::Grace->label())->toBe('فترة سماح')
        ->and(BillingCycle::Yearly->label())->toBe('سنوي')
        ->and(SubscriptionInvoiceStatus::PartiallyPaid->label())->toBe('مدفوعة جزئياً')
        ->and(AddOnStatus::Archived->label())->toBe('مؤرشفة')
        ->and(CompanyAddOnStatus::Expired->label())->toBe('منتهية');
});

it('provides English labels for SaaS and marketplace enums', function () {
    app()->setLocale('en');

    expect(PlanStatus::Inactive->label())->toBe('Inactive')
        ->and(SubscriptionStatus::PastDue->label())->toBe('Past due')
        ->and(BillingCycle::Monthly->label())->toBe('Monthly')
        ->and(SubscriptionInvoiceStatus::Voided->label())->toBe('Voided')
        ->and(AddOnStatus::Active->label())->toBe('Active')
        ->and(CompanyAddOnStatus::Cancelled->label())->toBe('Cancelled');
});
