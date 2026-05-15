<?php

namespace App\Jobs;

use App\Models\ExportJob;
use App\Notifications\ReportExportReadyNotification;
use App\Services\ReportExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessReportExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $exportJobId) {}

    public function handle(ReportExportService $exports): void
    {
        $job = ExportJob::query()->with('user')->findOrFail($this->exportJobId);
        $exports->process($job);

        $job->user?->notify((new ReportExportReadyNotification($job->refresh()))->afterCommit());
    }

    public function failed(?Throwable $exception): void
    {
        $job = ExportJob::query()->find($this->exportJobId);

        if ($job === null) {
            return;
        }

        app(ReportExportService::class)->markFailed($job, $exception?->getMessage() ?? 'Report export failed.');

        Log::error('Report export job failed.', [
            'export_job_id' => $this->exportJobId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
