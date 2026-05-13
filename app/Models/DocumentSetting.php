<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\DocumentSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'default_expiry_reminder_days',
    'allowed_file_types',
    'max_file_size',
    'document_approval_required',
    'metadata',
])]
class DocumentSetting extends Model
{
    /** @use HasFactory<DocumentSettingFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_expiry_reminder_days' => 'integer',
            'allowed_file_types' => 'array',
            'max_file_size' => 'integer',
            'document_approval_required' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
