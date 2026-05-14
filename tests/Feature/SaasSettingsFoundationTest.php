<?php

use App\Models\SaasSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores SaaS platform settings without tenant ownership', function () {
    $settings = SaasSetting::factory()->create([
        'default_trial_days' => 30,
        'default_currency' => 'SAR',
        'billing_enabled' => true,
        'marketplace_enabled' => true,
        'invoice_numbering_prefix' => 'NC-INV',
        'subscription_grace_period_days' => 10,
        'metadata' => ['owner' => 'system'],
    ]);

    expect($settings->default_trial_days)->toBe(30)
        ->and($settings->default_currency)->toBe('SAR')
        ->and($settings->billing_enabled)->toBeTrue()
        ->and($settings->marketplace_enabled)->toBeTrue()
        ->and($settings->subscription_grace_period_days)->toBe(10)
        ->and($settings->metadata)->toBe(['owner' => 'system'])
        ->and($settings->getAttributes())->not->toHaveKey('company_id');
});
