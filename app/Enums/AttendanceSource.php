<?php

namespace App\Enums;

enum AttendanceSource: string
{
    case Manual = 'manual';
    case Web = 'web';
    case Device = 'device';
    case Import = 'import';

    public function label(): string
    {
        return __("hr.attendance_sources.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $source): string => $source->value,
            self::cases(),
        );
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $source): array => [
                'value' => $source->value,
                'label' => $source->label(),
            ],
            self::cases(),
        );
    }
}
