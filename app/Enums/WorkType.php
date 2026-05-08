<?php

namespace App\Enums;

enum WorkType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Remote = 'remote';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return __("hr.work_types.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $workType): string => $workType->value,
            self::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $workType): array => [
                'value' => $workType->value,
                'label' => $workType->label(),
            ],
            self::cases(),
        );
    }
}
