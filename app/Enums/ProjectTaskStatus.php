<?php

namespace App\Enums;

enum ProjectTaskStatus: string
{
    case Todo = 'todo';
    case PendingApproval = 'pending_approval';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __("crm.project_task_statuses.{$this->value}");
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
