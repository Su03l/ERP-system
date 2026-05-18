<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\User;
use App\Notifications\DocumentExpiryNotification;
use App\Services\DocumentExpiryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScanDocumentExpiryNotificationsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public ?int $companyId = null,
    ) {}

    public function uniqueId(): string
    {
        return 'document-expiry-scan:'.($this->companyId ?? 'all').':'.now()->toDateString();
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [300, 900, 1800];
    }

    public function handle(DocumentExpiryService $expiryService): void
    {
        $companies = Company::query()
            ->when($this->companyId !== null, fn ($query) => $query->whereKey($this->companyId))
            ->with('documentSetting', 'users')
            ->get();

        foreach ($companies as $company) {
            $days = $company->documentSetting?->default_expiry_reminder_days ?? 30;
            $this->notifyDocuments($company, $expiryService->expired($company), 'expired');
            $this->notifyDocuments($company, $expiryService->expiringWithin($days, $company), 'expiring');
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Document expiry notification scan failed.', [
            'company_id' => $this->companyId,
            'message' => $exception?->getMessage(),
        ]);
    }

    private function notifyDocuments(Company $company, mixed $groups, string $state): void
    {
        $users = $company->users;

        foreach ($groups as $documents) {
            foreach ($documents as $document) {
                foreach ($users as $user) {
                    if (! $this->alreadyNotified($user, $document, $state)) {
                        $user->notify(new DocumentExpiryNotification($document, $state));
                    }
                }
            }
        }
    }

    private function alreadyNotified(User $user, Model $document, string $state): bool
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->where('type', DocumentExpiryNotification::class)
            ->where('data->state', $state)
            ->where('data->document_model', $document->getMorphClass())
            ->where('data->document_id', $document->getKey())
            ->where('data->reminder_date', now()->toDateString())
            ->exists();
    }
}
