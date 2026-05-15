<?php

namespace App\Contracts;

use App\DTOs\ReportDefinition;
use App\DTOs\ReportFilter;

interface ReportResolver
{
    public function definition(): ReportDefinition;

    /**
     * @return array<string, mixed>
     */
    public function resolve(ReportFilter $filter): array;
}
