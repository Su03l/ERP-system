<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\ProjectCrmSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectCrmSetting>
 */
class ProjectCrmSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'project_code_prefix' => 'PRJ',
            'task_code_prefix' => 'TSK',
            'default_project_status' => ProjectStatus::Draft,
            'project_approval_required' => true,
            'task_approval_required' => false,
            'time_tracking_enabled' => true,
            'billable_time_enabled' => false,
            'crm_enabled' => true,
            'metadata' => [],
        ];
    }
}
