<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\AnalyticsSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'default_dashboard_date_range',
    'kpi_refresh_frequency',
    'export_language',
    'pdf_export_enabled',
    'excel_export_enabled',
    'dashboard_widgets_enabled',
    'metadata',
])]
class AnalyticsSetting extends Model
{
    /** @use HasFactory<AnalyticsSettingFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pdf_export_enabled' => 'boolean',
            'excel_export_enabled' => 'boolean',
            'dashboard_widgets_enabled' => 'array',
            'metadata' => 'array',
        ];
    }
}
