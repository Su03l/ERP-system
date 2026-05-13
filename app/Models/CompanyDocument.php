<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\CompanyDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'document_type',
    'title_ar',
    'title_en',
    'file_path',
    'issue_date',
    'expiry_date',
    'status',
    'notes_ar',
    'notes_en',
    'metadata',
])]
class CompanyDocument extends Model
{
    /** @use HasFactory<CompanyDocumentFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'metadata' => 'array',
        ];
    }
}
