<?php

namespace App\Enums;

enum CompanyModule: string
{
    case Hr = 'hr';
    case Attendance = 'attendance';
    case Leave = 'leave';
    case Payroll = 'payroll';
    case Accounting = 'accounting';
    case Assets = 'assets';
    case Documents = 'documents';
    case Projects = 'projects';
    case Workflow = 'workflow';
    case Analytics = 'analytics';
    case Marketplace = 'marketplace';

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_map(
            fn (self $module): string => $module->value,
            self::cases(),
        );
    }
}
