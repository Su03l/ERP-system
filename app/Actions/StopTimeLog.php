<?php

namespace App\Actions;

use App\Models\ProjectTimeLog;
use App\Models\User;
use Carbon\CarbonInterface;

class StopTimeLog
{
    public function __construct(private readonly UpdateTimeLog $updateTimeLog) {}

    public function handle(ProjectTimeLog $timeLog, ?User $actor = null, ?CarbonInterface $stoppedAt = null): ProjectTimeLog
    {
        $stoppedAt ??= now();

        return $this->updateTimeLog->handle($timeLog, [
            'end_time' => $stoppedAt->format('H:i'),
        ], $actor);
    }
}
