<?php

namespace App\Services;

use App\Contracts\KpiResolver;
use App\DTOs\KpiDateRange;
use App\DTOs\KpiResult;
use App\Models\Company;
use InvalidArgumentException;

class KpiRegistry
{
    /**
     * @param  iterable<KpiResolver>  $resolvers
     */
    public function __construct(private iterable $resolvers = []) {}

    /**
     * @return array<int, array{key: string, label: string, category: string}>
     */
    public function available(): array
    {
        return collect($this->resolvers)
            ->map(fn (KpiResolver $resolver): array => [
                'key' => $resolver->key(),
                'label' => $resolver->label(),
                'category' => $resolver->category(),
            ])
            ->values()
            ->all();
    }

    public function resolve(string $key, Company $company, KpiDateRange $dateRange): KpiResult
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->key() === $key) {
                return $resolver->resolve($company, $dateRange);
            }
        }

        throw new InvalidArgumentException("KPI resolver [{$key}] is not registered.");
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<int, array<string, mixed>>
     */
    public function export(array $keys, Company $company, KpiDateRange $dateRange): array
    {
        return collect($keys)
            ->map(fn (string $key): array => $this->resolve($key, $company, $dateRange)->toArray())
            ->values()
            ->all();
    }
}
