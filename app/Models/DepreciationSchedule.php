<?php

namespace App\Models;

use App\Enums\DepreciationScheduleStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\DepreciationScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'company_id',
    'asset_id',
    'period_date',
    'depreciation_amount',
    'accumulated_depreciation',
    'book_value',
    'status',
    'posted_journal_entry_id',
    'metadata',
])]
class DepreciationSchedule extends Model
{
    /** @use HasFactory<DepreciationScheduleFactory> */
    use BelongsToCompany, HasFactory;

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** @return BelongsTo<JournalEntry, $this> */
    public function postedJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'posted_journal_entry_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'depreciation_amount' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'book_value' => 'decimal:2',
            'status' => DepreciationScheduleStatus::class,
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (DepreciationSchedule $schedule): void {
            $asset = Asset::query()->find($schedule->asset_id);

            if ($asset === null || (int) $asset->company_id !== (int) $schedule->company_id) {
                throw ValidationException::withMessages([
                    'asset_id' => __('assets.validation.depreciation_schedules.asset_company'),
                ]);
            }
        });
    }
}
