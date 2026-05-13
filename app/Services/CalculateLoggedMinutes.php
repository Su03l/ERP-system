<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class CalculateLoggedMinutes
{
    public function handle(string $startTime, string $endTime): int
    {
        $start = CarbonImmutable::createFromFormat('H:i', substr($startTime, 0, 5));
        $end = CarbonImmutable::createFromFormat('H:i', substr($endTime, 0, 5));

        if ($start === false || $end === false || $end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages([
                'end_time' => __('validation.after', ['attribute' => 'end_time', 'date' => 'start_time']),
            ]);
        }

        return (int) $start->diffInMinutes($end);
    }
}
