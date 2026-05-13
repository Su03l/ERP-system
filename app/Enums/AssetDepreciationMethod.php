<?php

namespace App\Enums;

enum AssetDepreciationMethod: string
{
    case StraightLine = 'straight_line';
    case DecliningBalance = 'declining_balance';
    case UnitsOfProduction = 'units_of_production';

    public function label(): string
    {
        return __("assets.depreciation_methods.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $method): string => $method->value,
            self::cases(),
        );
    }
}
