<?php

namespace App\Enums;

enum DocumentOwnerType: string
{
    case Company = 'company';
    case Employee = 'employee';
    case Asset = 'asset';
    case Vendor = 'vendor';
    case Customer = 'customer';

    public function label(): string
    {
        return __("documents.owner_types.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}
