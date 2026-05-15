<?php

use App\Models\Permission;
use App\Support\PlatformAbilities;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds analytics report and KPI permissions', function () {
    $this->artisan('migrate');

    expect(Permission::query()->where('key', 'analytics.view')->exists())->toBeTrue()
        ->and(Permission::query()->where('key', 'reports.export')->exists())->toBeTrue()
        ->and(Permission::query()->where('key', 'kpi.payroll.view')->exists())->toBeTrue();
});

it('treats SaaS KPI permission as platform scoped', function () {
    expect(PlatformAbilities::contains('kpi.saas.view'))->toBeTrue();
});
