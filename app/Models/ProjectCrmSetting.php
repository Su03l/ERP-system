<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ProjectCrmSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'project_code_prefix',
    'task_code_prefix',
    'default_project_status',
    'project_approval_required',
    'task_approval_required',
    'time_tracking_enabled',
    'billable_time_enabled',
    'crm_enabled',
    'metadata',
])]
class ProjectCrmSetting extends Model
{
    /** @use HasFactory<ProjectCrmSettingFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'project_approval_required' => 'boolean',
            'task_approval_required' => 'boolean',
            'time_tracking_enabled' => 'boolean',
            'billable_time_enabled' => 'boolean',
            'crm_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
