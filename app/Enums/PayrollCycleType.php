<?php

namespace App\Enums;

enum PayrollCycleType: string
{
    case Monthly = 'monthly';
    case SemiMonthly = 'semi_monthly';
    case Weekly = 'weekly';

    public function label(): string
    {
        return __("payroll.cycle_types.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $type): string => $type->value,
            self::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases(),
        );
    }
}
