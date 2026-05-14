<?php

namespace App\Enums;

enum SubscriptionBillingCycle: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return __("saas.subscription_billing_cycles.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $cycle): string => $cycle->value, self::cases());
    }
}
