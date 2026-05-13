<?php

namespace App\Models;

use App\Enums\CustodyStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\AssetCustodyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'company_id',
    'asset_id',
    'employee_id',
    'assigned_by',
    'assigned_at',
    'returned_at',
    'return_received_by',
    'status',
    'notes_ar',
    'notes_en',
    'workflow_instance_id',
    'metadata',
])]
class AssetCustody extends Model
{
    /** @use HasFactory<AssetCustodyFactory> */
    use BelongsToCompany, HasFactory;

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** @return BelongsTo<User, $this> */
    public function returnReceivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_received_by');
    }

    /** @return BelongsTo<WorkflowInstance, $this> */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'returned_at' => 'datetime',
            'status' => CustodyStatus::class,
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (AssetCustody $custody): void {
            $asset = Asset::query()->find($custody->asset_id);
            $employee = Employee::query()->find($custody->employee_id);

            if ($asset === null || (int) $asset->company_id !== (int) $custody->company_id) {
                throw ValidationException::withMessages([
                    'asset_id' => __('assets.validation.asset_custodies.asset_company'),
                ]);
            }

            if ($employee === null || (int) $employee->company_id !== (int) $custody->company_id) {
                throw ValidationException::withMessages([
                    'employee_id' => __('assets.validation.asset_custodies.employee_company'),
                ]);
            }
        });
    }
}
