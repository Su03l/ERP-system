<?php

namespace App\Enums;

enum CompanyAddOnStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return __("saas.company_add_on_statuses.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
