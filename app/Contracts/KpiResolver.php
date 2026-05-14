<?php

namespace App\Contracts;

use App\DTOs\KpiDateRange;
use App\DTOs\KpiDefinition;
use App\DTOs\KpiResult;
use App\Models\Company;

interface KpiResolver
{
    public function definition(): KpiDefinition;

    public function key(): string;

    public function label(): string;

    public function module(): string;

    public function resolve(Company $company, KpiDateRange $dateRange): KpiResult;
}
