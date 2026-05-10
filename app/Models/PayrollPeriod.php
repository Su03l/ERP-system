<?php

namespace App\Models;

use App\Enums\PayrollPeriodStatus;
use App\Models\Concerns\BelongsToCompany;
use Carbon\CarbonInterface;
use Database\Factories\PayrollPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'company_id',
    'name_ar',
    'name_en',
    'starts_on',
    'ends_on',
    'pay_date',
    'status',
    'closed_at',
    'closed_by',
    'metadata',
])]
class PayrollPeriod extends Model
{
    /** @use HasFactory<PayrollPeriodFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the user who closed this payroll period.
     *
     * @return BelongsTo<User, $this>
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get payroll runs created for this period.
     *
     * @return HasMany<PayrollRun, $this>
     */
    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    /**
     * Scope periods that overlap the provided date range.
     *
     * @param  Builder<PayrollPeriod>  $query
     * @return Builder<PayrollPeriod>
     */
    public function scopeOverlapping(Builder $query, string|CarbonInterface $startsOn, string|CarbonInterface $endsOn): Builder
    {
        return $query
            ->where('starts_on', '<=', $endsOn)
            ->where('ends_on', '>=', $startsOn);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'pay_date' => 'date',
            'status' => PayrollPeriodStatus::class,
            'closed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $period): void {
            if ($period->starts_on === null || $period->ends_on === null) {
                return;
            }

            $overlapExists = self::query()
                ->where('company_id', $period->company_id)
                ->when($period->exists, fn (Builder $query): Builder => $query->whereKeyNot($period->getKey()))
                ->overlapping($period->starts_on, $period->ends_on)
                ->exists();

            if ($overlapExists) {
                throw ValidationException::withMessages([
                    'starts_on' => __('validation.custom.payroll_periods.overlap'),
                ]);
            }
        });
    }
}
