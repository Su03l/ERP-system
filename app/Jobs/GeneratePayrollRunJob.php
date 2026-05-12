<?php

namespace App\Jobs;

use App\Actions\GeneratePayrollRun;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\PayrollGenerationQueueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeneratePayrollRunJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public int $payrollPeriodId,
        public int $actorId,
        public int $companyId,
        public array $data = [],
    ) {}

    public function uniqueId(): string
    {
        return "payroll-generation:{$this->companyId}:{$this->payrollPeriodId}";
    }

    public function handle(GeneratePayrollRun $generatePayrollRun, PayrollGenerationQueueService $queueService): void
    {
        $period = PayrollPeriod::query()
            ->where('company_id', $this->companyId)
            ->findOrFail($this->payrollPeriodId);
        $actor = User::query()
            ->where('company_id', $this->companyId)
            ->findOrFail($this->actorId);

        Auth::login($actor);

        $queueService->updateStatus($period, 'processing', $this->uniqueId());
        $generatePayrollRun->handle($period, $this->data, $actor);
        $queueService->updateStatus($period->refresh(), 'completed', $this->uniqueId());
    }

    public function failed(?Throwable $exception): void
    {
        $period = PayrollPeriod::query()
            ->where('company_id', $this->companyId)
            ->find($this->payrollPeriodId);

        if ($period === null) {
            return;
        }

        app(PayrollGenerationQueueService::class)->updateStatus(
            $period,
            'failed',
            $this->uniqueId(),
            $exception?->getMessage(),
        );

        Log::error('Queued payroll generation failed.', [
            'company_id' => $this->companyId,
            'payroll_period_id' => $this->payrollPeriodId,
            'actor_id' => $this->actorId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
