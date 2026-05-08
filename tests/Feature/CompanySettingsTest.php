<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use App\Services\CompanySettingsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('company settings can be read and updated for current tenant', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user);

    $updatedCompany = app(CompanySettingsService::class)->update($company, [
        'name' => 'Nawwat Demo Company',
        'legal_name' => 'Nawwat Demo Company LLC',
        'email' => 'settings@nawwat.test',
        'phone' => '+966500000000',
        'locale' => 'ar',
        'timezone' => 'Asia/Riyadh',
        'currency' => 'SAR',
        'date_preference' => 'gregorian',
        'working_days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'],
        'branding' => [
            'logo_path' => null,
            'primary_color' => '#14532d',
        ],
        'notification_preferences' => [
            'email_enabled' => true,
            'database_enabled' => true,
            'sms_enabled' => false,
        ],
    ]);

    expect($updatedCompany->name)->toBe('Nawwat Demo Company')
        ->and($updatedCompany->locale)->toBe('ar')
        ->and($updatedCompany->timezone)->toBe('Asia/Riyadh')
        ->and($updatedCompany->currency)->toBe('SAR')
        ->and($updatedCompany->settings['date_preference'])->toBe('gregorian')
        ->and($updatedCompany->settings['working_days'])->toBe(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'])
        ->and($updatedCompany->settings['branding']['primary_color'])->toBe('#14532d')
        ->and($updatedCompany->settings['notification_preferences']['sms_enabled'])->toBeFalse();

    $auditLog = AuditLog::where('action', 'company.settings.updated')->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->company_id)->toBe($company->id)
        ->and($auditLog->user_id)->toBe($user->id);
});

test('company settings cannot be updated outside current tenant', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($company)->create();

    $this->actingAs($user);

    app(CompanySettingsService::class)->update($otherCompany, [
        'name' => 'Should Not Update',
    ]);
})->throws(AuthorizationException::class);

test('company settings can be read for current tenant', function () {
    $company = Company::factory()->create([
        'settings' => ['date_preference' => 'hijri'],
    ]);
    $user = User::factory()->for($company)->create();

    $this->actingAs($user);

    $settings = app(CompanySettingsService::class)->read($company);

    expect($settings['name'])->toBe($company->name)
        ->and($settings['settings']['date_preference'])->toBe('hijri');
});
