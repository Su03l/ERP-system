<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AssetExportQuery
{
    /**
     * @param  array{asset_category_id?: int, category?: int, status?: string, assigned_employee_id?: int, assigned_employee?: int, search?: string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function rows(array $filters, User $actor): array
    {
        if ($actor->company_id === null || ! $actor->hasPermission('assets.export', $actor->company_id)) {
            throw new AuthorizationException('You are not authorized to export assets.');
        }

        $categoryId = $filters['asset_category_id'] ?? $filters['category'] ?? null;
        $assignedEmployeeId = $filters['assigned_employee_id'] ?? $filters['assigned_employee'] ?? null;

        return Asset::query()
            ->with(['category', 'assignedEmployee'])
            ->forCompany($actor->company_id)
            ->when($categoryId, fn ($query, int $assetCategoryId) => $query->where('asset_category_id', $assetCategoryId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($assignedEmployeeId, fn ($query, int $employeeId) => $query->where('assigned_employee_id', $employeeId))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(function ($query) use ($search): void {
                $query->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            }))
            ->orderBy('asset_code')
            ->get()
            ->map(fn (Asset $asset): array => [
                'asset_code' => $asset->asset_code,
                'name_ar' => $asset->name_ar,
                'name_en' => $asset->name_en,
                'category_ar' => $asset->category?->name_ar,
                'category_en' => $asset->category?->name_en,
                'status' => $asset->status?->value,
                'status_label' => $asset->status?->label(),
                'serial_number' => $asset->serial_number,
                'location' => $asset->location,
                'assigned_employee_number' => $asset->assignedEmployee?->employee_number,
                'assigned_employee_name_ar' => $asset->assignedEmployee?->first_name_ar.' '.$asset->assignedEmployee?->last_name_ar,
                'purchase_date' => $asset->purchase_date?->toDateString(),
                'purchase_cost' => $asset->purchase_cost,
                'current_value' => $asset->current_value,
            ])
            ->all();
    }
}
