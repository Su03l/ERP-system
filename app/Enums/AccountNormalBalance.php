<?php

namespace App\Enums;

enum AccountNormalBalance: string
{
    case Debit = 'debit';
    case Credit = 'credit';

    public function label(): string
    {
        return __("accounting.normal_balances.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            fn (self $balance): string => $balance->value,
            self::cases(),
        );
    }
}
