<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ExportJobFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'user_id',
    'status',
    'file_path',
    'entity_type',
    'module_key',
    'error_summary',
    'processed_rows',
    'failed_rows',
    'total_rows',
    'started_at',
    'finished_at',
])]
class ExportJob extends Model
{
    /** @use HasFactory<ExportJobFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'error_summary' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
