<?php

namespace App\Enums;

enum DocumentType: string
{
    case Contract = 'contract';
    case License = 'license';
    case Certificate = 'certificate';
    case Policy = 'policy';
    case Identification = 'identification';
    case NationalId = 'national_id';
    case Passport = 'passport';
    case Other = 'other';

    public function label(): string
    {
        return __("documents.document_types.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}
