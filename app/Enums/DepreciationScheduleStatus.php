<?php

namespace App\Enums;

enum DepreciationScheduleStatus: string
{
    case Draft = 'draft';
    case Calculated = 'calculated';
    case Posted = 'posted';

    public function label(): string
    {
        return __("assets.depreciation_schedule_statuses.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $status): string => $status->value,
            self::cases(),
        );
    }
}
