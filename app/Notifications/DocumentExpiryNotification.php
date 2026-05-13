<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class DocumentExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Model $document,
        public readonly string $state,
    ) {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'document_expiry',
            'state' => $this->state,
            'company_id' => $this->document->company_id,
            'document_type' => $this->document->document_type?->value ?? $this->document->document_type,
            'document_model' => $this->document->getMorphClass(),
            'document_id' => $this->document->getKey(),
            'title_ar' => $this->document->title_ar,
            'title_en' => $this->document->title_en,
            'expiry_date' => $this->document->expiry_date?->toDateString(),
            'reminder_date' => now()->toDateString(),
        ];
    }
}
