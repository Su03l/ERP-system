<?php

namespace Database\Factories;

use App\Models\AnalyticsSetting;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsSetting>
 */
class AnalyticsSettingFactory extends Factory
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
            'default_dashboard_date_range' => 'this_month',
            'kpi_refresh_frequency' => 'daily',
            'export_language' => 'ar',
            'pdf_export_enabled' => true,
            'excel_export_enabled' => true,
            'dashboard_widgets_enabled' => ['hr', 'attendance', 'leave'],
            'metadata' => [],
        ];
    }
}
