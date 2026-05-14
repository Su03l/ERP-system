<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSubscriptionExpiriesJob;
use App\Services\SubscriptionExpiryService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('subscriptions:process-expiries {--company= : Limit processing to one company id} {--sync : Run immediately instead of dispatching a queued job}')]
#[Description('Process expired trials and subscriptions, apply grace periods, and notify company admins.')]
class ProcessSubscriptionExpiriesCommand extends Command
{
    public function handle(SubscriptionExpiryService $expiryService): int
    {
        $companyId = $this->option('company') !== null ? (int) $this->option('company') : null;

        if ($this->option('sync')) {
            $summary = $expiryService->process($companyId);

            $this->components->info('Subscription expiries processed.');
            $this->line(json_encode($summary, JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        ProcessSubscriptionExpiriesJob::dispatch($companyId);
        $this->components->info('Subscription expiry processing job dispatched.');

        return self::SUCCESS;
    }
}
