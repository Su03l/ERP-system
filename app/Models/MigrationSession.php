<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\MigrationSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'user_id',
    'import_job_id',
    'uploaded_file_path',
    'target_entity',
    'module_key',
    'column_mapping',
    'validation_result',
    'dry_run_status',
    'final_import_status',
])]
class MigrationSession extends Model
{
    /** @use HasFactory<MigrationSessionFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<ImportJob, $this>
     */
    public function importJob(): BelongsTo
    {
        return $this->belongsTo(ImportJob::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'column_mapping' => 'array',
            'validation_result' => 'array',
        ];
    }
}
