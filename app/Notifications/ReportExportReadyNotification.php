<?php

namespace App\Notifications;

use App\Models\ExportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportExportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ExportJob $exportJob)
    {
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
            'type' => 'report_export_ready',
            'export_job_id' => $this->exportJob->id,
            'company_id' => $this->exportJob->company_id,
            'report_key' => $this->exportJob->entity_type,
            'module_key' => $this->exportJob->module_key,
            'status' => $this->exportJob->status,
            'file_path' => $this->exportJob->file_path,
            'total_rows' => $this->exportJob->total_rows,
        ];
    }
}
