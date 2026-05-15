<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\DashboardPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'user_id',
    'dashboard_key',
    'selected_widgets',
    'widget_order',
    'hidden_widgets',
    'filters',
    'metadata',
])]
class DashboardPreference extends Model
{
    /** @use HasFactory<DashboardPreferenceFactory> */
    use BelongsToCompany, HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'selected_widgets' => 'array',
            'widget_order' => 'array',
            'hidden_widgets' => 'array',
            'filters' => 'array',
            'metadata' => 'array',
        ];
    }
}
