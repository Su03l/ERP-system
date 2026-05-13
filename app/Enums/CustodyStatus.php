<?php

namespace App\Enums;

enum CustodyStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case Returned = 'returned';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __("assets.custody_statuses.{$this->value}");
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

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases(),
        );
    }
}
