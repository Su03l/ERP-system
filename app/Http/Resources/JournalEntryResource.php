<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
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
            'journal_number' => $this->journal_number,
            'entry_date' => $this->entry_date?->toDateString(),
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'source' => $this->source?->value,
            'source_label' => $this->source?->label(),
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'posted_by' => $this->posted_by,
            'posted_at' => $this->posted_at,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'workflow_instance_id' => $this->workflow_instance_id,
            'debit_total' => $this->when($this->relationLoaded('lines'), fn (): string => $this->debitTotal()),
            'credit_total' => $this->when($this->relationLoaded('lines'), fn (): string => $this->creditTotal()),
            'metadata' => $this->metadata,
            'lines' => $this->whenLoaded('lines', fn () => $this->lines->map(fn ($line): array => [
                'id' => $line->id,
                'account_id' => $line->account_id,
                'description_ar' => $line->description_ar,
                'description_en' => $line->description_en,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'line_order' => $line->line_order,
                'metadata' => $line->metadata,
            ])->values()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
