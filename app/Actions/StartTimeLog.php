<?php

namespace App\Actions;

use App\Models\ProjectTimeLog;
use App\Models\User;
use Carbon\CarbonInterface;

class StartTimeLog
{
    public function __construct(private readonly CreateManualTimeLog $createManualTimeLog) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, ?User $actor = null, ?CarbonInterface $startedAt = null): ProjectTimeLog
    {
        $startedAt ??= now();

        return $this->createManualTimeLog->handle([
            ...$data,
            'log_date' => $data['log_date'] ?? $startedAt->toDateString(),
            'start_time' => $data['start_time'] ?? $startedAt->format('H:i'),
            'end_time' => null,
            'total_minutes' => 0,
        ], $actor);
    }
}
