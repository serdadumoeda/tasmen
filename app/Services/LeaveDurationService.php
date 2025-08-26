<?php

namespace App\Services;

use App\Models\CutiBersama;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveDurationService
{
    /**
     * Calculate the duration of a leave request in business days,
     * excluding weekends and collective leave days (Cuti Bersama).
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return int
     */
    public static function calculate($startDate, $endDate): int
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Fetch all collective leave dates within the period for efficiency
        $collectiveLeaveDates = CutiBersama::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        $period = CarbonPeriod::create($startDate, $endDate);
        $duration = 0;

        foreach ($period as $date) {
            // Check if the day is a weekend or a collective leave day
            if ($date->isWeekend() || in_array($date->format('Y-m-d'), $collectiveLeaveDates)) {
                continue;
            }
            $duration++;
        }

        return $duration;
    }
}
