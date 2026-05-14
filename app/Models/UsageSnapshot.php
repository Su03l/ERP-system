<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\UsageSnapshotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'users_count',
    'employees_count',
    'storage_usage_mb',
    'active_modules_count',
    'api_requests_count',
    'exports_count',
    'metadata',
    'captured_at',
])]
class UsageSnapshot extends Model
{
    /** @use HasFactory<UsageSnapshotFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'users_count' => 'integer',
            'employees_count' => 'integer',
            'storage_usage_mb' => 'integer',
            'active_modules_count' => 'integer',
            'api_requests_count' => 'integer',
            'exports_count' => 'integer',
            'metadata' => 'array',
            'captured_at' => 'datetime',
        ];
    }
}
