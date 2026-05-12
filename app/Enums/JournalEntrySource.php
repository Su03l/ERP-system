<?php

namespace App\Enums;

enum JournalEntrySource: string
{
    case Manual = 'manual';
    case Payroll = 'payroll';
    case Invoice = 'invoice';
    case Payment = 'payment';
    case Adjustment = 'adjustment';
    case Import = 'import';

    public function label(): string
    {
        return __("accounting.journal_entry_sources.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $source): string => $source->value, self::cases());
    }
}
