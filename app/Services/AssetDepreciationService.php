<?php

namespace App\Services;

use App\Enums\AssetDepreciationMethod;
use App\Models\Asset;
use Carbon\CarbonInterface;

class AssetDepreciationService
{
    /**
     * @return array{depreciation_amount: string, accumulated_depreciation: string, book_value: string}
     */
    public function calculateStraightLine(Asset $asset, CarbonInterface $periodDate): array
    {
        $purchaseCost = (float) ($asset->purchase_cost ?? 0);
        $salvageValue = (float) ($asset->salvage_value ?? 0);
        $usefulLifeMonths = max((int) ($asset->useful_life_months ?? 1), 1);
        $depreciableAmount = max($purchaseCost - $salvageValue, 0);
        $monthlyDepreciation = round($depreciableAmount / $usefulLifeMonths, 2);
        $elapsedMonths = $this->elapsedMonths($asset, $periodDate);
        $accumulated = min(round($monthlyDepreciation * $elapsedMonths, 2), $depreciableAmount);
        $bookValue = max(round($purchaseCost - $accumulated, 2), $salvageValue);

        return [
            'depreciation_amount' => $this->money($monthlyDepreciation),
            'accumulated_depreciation' => $this->money($accumulated),
            'book_value' => $this->money($bookValue),
        ];
    }

    /**
     * @return array{depreciation_amount: string, accumulated_depreciation: string, book_value: string}
     */
    public function calculate(Asset $asset, CarbonInterface $periodDate): array
    {
        return match ($asset->depreciation_method) {
            AssetDepreciationMethod::StraightLine, null => $this->calculateStraightLine($asset, $periodDate),
            default => $this->calculateStraightLine($asset, $periodDate),
        };
    }

    private function elapsedMonths(Asset $asset, CarbonInterface $periodDate): int
    {
        if ($asset->purchase_date === null) {
            return 1;
        }

        return max($asset->purchase_date->startOfMonth()->diffInMonths($periodDate->copy()->startOfMonth()) + 1, 1);
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
