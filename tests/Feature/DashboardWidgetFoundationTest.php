<?php

use App\Models\Company;
use App\Models\DashboardPreference;
use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('stores tenant and platform dashboard widgets', function () {
    expect(Schema::hasColumns('dashboard_widgets', [
        'id',
        'company_id',
        'widget_key',
        'module',
        'title_ar',
        'title_en',
        'type',
        'resolver',
        'required_permission',
        'default_size',
        'metadata',
    ]))->toBeTrue();

    $tenantWidget = DashboardWidget::factory()->create();
    $platformWidget = DashboardWidget::factory()->create([
        'company_id' => null,
        'widget_key' => 'saas.mrr',
        'module' => 'saas',
        'resolver' => 'saas.mrr',
        'required_permission' => 'subscription_invoices.view',
    ]);

    expect($tenantWidget->company_id)->not->toBeNull()
        ->and($platformWidget->company_id)->toBeNull();
});

it('stores user dashboard preferences with ordering visibility and filters', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $preference = DashboardPreference::factory()->for($company)->for($user)->create([
        'selected_widgets' => ['hr.total_employees', 'attendance.attendance_rate'],
        'widget_order' => ['attendance.attendance_rate', 'hr.total_employees'],
        'hidden_widgets' => ['hr.total_employees'],
        'filters' => ['date_range' => 'this_month'],
    ]);

    expect($preference->company->is($company))->toBeTrue()
        ->and($preference->user->is($user))->toBeTrue()
        ->and($preference->filters)->toBe(['date_range' => 'this_month']);
});
