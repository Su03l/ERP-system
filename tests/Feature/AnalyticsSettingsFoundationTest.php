<?php

use App\Models\AnalyticsSetting;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('creates tenant scoped analytics settings with Arabic export defaults', function () {
    expect(Schema::hasColumns('analytics_settings', [
        'id',
        'company_id',
        'default_dashboard_date_range',
        'kpi_refresh_frequency',
        'export_language',
        'pdf_export_enabled',
        'excel_export_enabled',
        'dashboard_widgets_enabled',
        'metadata',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    $company = Company::factory()->create();
    $settings = AnalyticsSetting::factory()->for($company)->create();

    expect($settings->company->is($company))->toBeTrue()
        ->and($settings->export_language)->toBe('ar')
        ->and($settings->pdf_export_enabled)->toBeTrue()
        ->and($settings->excel_export_enabled)->toBeTrue()
        ->and($settings->dashboard_widgets_enabled)->toBe(['hr', 'attendance', 'leave']);
});

it('connects analytics settings to companies', function () {
    $company = Company::factory()->create();
    $settings = AnalyticsSetting::factory()->for($company)->create([
        'default_dashboard_date_range' => 'last_30_days',
    ]);

    expect($company->analyticsSetting()->first()->is($settings))->toBeTrue();
});
