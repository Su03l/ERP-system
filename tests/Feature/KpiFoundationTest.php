<?php

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;
use App\Services\KpiRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('kpi registry resolves export ready results for a company date range', function () {
    $company = Company::factory()->create();
    $dateRange = KpiDateRange::fromDates('2026-01-01', '2026-01-31');
    $registry = new KpiRegistry([
        new class implements KpiResolver
        {
            public function definition(): KpiDefinition
            {
                return new KpiDefinition(
                    key: $this->key(),
                    module: $this->module(),
                    labelAr: 'عدد الموظفين',
                    labelEn: $this->label(),
                    descriptionAr: null,
                    descriptionEn: null,
                    requiredPermission: 'employees.view',
                    resolverClass: self::class,
                );
            }

            public function key(): string
            {
                return 'hr.headcount';
            }

            public function label(): string
            {
                return 'Headcount';
            }

            public function module(): string
            {
                return 'hr';
            }

            public function resolve(Company $company, KpiDateRange $dateRange): KpiResult
            {
                return new KpiResult(
                    key: $this->key(),
                    label: $this->label(),
                    value: 0,
                    category: $this->module(),
                    dateRange: $dateRange,
                    metadata: ['company_id' => $company->id],
                );
            }
        },
    ]);

    $available = $registry->available()[0];

    expect($available['key'])->toBe('hr.headcount')
        ->and($available['module'])->toBe('hr')
        ->and($available['label_ar'])->toBe('عدد الموظفين')
        ->and($available['label_en'])->toBe('Headcount')
        ->and($available['required_permission'])->toBe('employees.view')
        ->and($available['supports_date_range'])->toBeTrue()
        ->and($available['default_date_range'])->toBeNull();

    $export = $registry->export(['hr.headcount'], $company, $dateRange);

    expect($export[0]['key'])->toBe('hr.headcount')
        ->and($export[0]['value'])->toBe(0)
        ->and($export[0]['date_range'])->toBe(['start' => '2026-01-01', 'end' => '2026-01-31'])
        ->and($export[0]['metadata']['company_id'])->toBe($company->id);
});

test('kpi date range rejects invalid ranges', function () {
    KpiDateRange::fromDates('2026-02-01', '2026-01-01');
})->throws(InvalidArgumentException::class);
