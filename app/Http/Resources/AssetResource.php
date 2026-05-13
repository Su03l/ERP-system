<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'asset_category_id' => $this->asset_category_id,
            'asset_code' => $this->asset_code,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'serial_number' => $this->serial_number,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'purchase_cost' => $this->purchase_cost,
            'current_value' => $this->current_value,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'location' => $this->location,
            'assigned_employee_id' => $this->assigned_employee_id,
            'depreciation_method' => $this->depreciation_method?->value,
            'depreciation_method_label' => $this->depreciation_method?->label(),
            'useful_life_months' => $this->useful_life_months,
            'salvage_value' => $this->salvage_value,
            'metadata' => $this->metadata,
            'category' => AssetCategoryResource::make($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toJSON(),
            'updated_at' => $this->updated_at?->toJSON(),
        ];
    }
}
