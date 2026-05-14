<?php

namespace App\Jobs;

use App\Services\SubscriptionExpiryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessSubscriptionExpiriesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public ?int $companyId = null) {}

    public function uniqueId(): string
    {
        return 'subscription-expiry-scan:'.($this->companyId ?? 'all').':'.now()->toDateString();
    }

    public function handle(SubscriptionExpiryService $expiryService): void
    {
        $expiryService->process($this->companyId);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Subscription expiry scan failed.', [
            'company_id' => $this->companyId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
