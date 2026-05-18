<?php

use App\Jobs\DispatchWebhookDelivery;
use App\Jobs\GeneratePayrollRunJob;
use App\Jobs\ProcessReportExportJob;
use App\Jobs\ProcessSubscriptionExpiriesJob;
use App\Jobs\ScanDocumentExpiryNotificationsJob;
use App\Models\WebhookDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps queued jobs retryable and tenant-aware where applicable', function (object $job, ?int $companyId = null) {
    expect($job->tries)->toBeGreaterThanOrEqual(3)
        ->and($job->backoff())->not->toBeEmpty();

    if ($companyId !== null) {
        expect($job->companyId)->toBe($companyId);
    }
})->with([
    [fn () => new GeneratePayrollRunJob(10, 20, 30), 30],
    [fn () => new ProcessReportExportJob(10), null],
    [fn () => new ScanDocumentExpiryNotificationsJob(30), 30],
    [fn () => new ProcessSubscriptionExpiriesJob(30), 30],
    [fn () => new DispatchWebhookDelivery(10), null],
]);

it('limits webhook failed job error messages before storing them', function () {
    $delivery = WebhookDelivery::factory()->create();
    $job = new DispatchWebhookDelivery($delivery->id);

    $job->failed(new Exception(str_repeat('x', 1000)));

    expect($delivery->refresh()->status)->toBe('failed')
        ->and(mb_strlen($delivery->error_message))->toBeLessThanOrEqual(503);
});
