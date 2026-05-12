<?php

namespace App\Enums;

enum PaymentDirection: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';

    public function label(): string
    {
        return __("accounting.payment_directions.{$this->value}");
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn (self $direction): string => $direction->value, self::cases());
    }
}
