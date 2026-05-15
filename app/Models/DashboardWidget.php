<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\DashboardWidgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'widget_key',
    'module',
    'title_ar',
    'title_en',
    'type',
    'resolver',
    'required_permission',
    'default_size',
    'metadata',
])]
class DashboardWidget extends Model
{
    /** @use HasFactory<DashboardWidgetFactory> */
    use BelongsToCompany, HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
