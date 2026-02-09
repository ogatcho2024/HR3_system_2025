<?php

namespace App\Services;

use Carbon\Carbon;

class NightDifferentialService
{
    /**
     * Compute night differential minutes for a given shift.
     * Night window (PH): 22:00 to 06:00 next day.
     */
    public function calculateMinutes(
        $workDate,
        ?string $clockIn,
        ?string $clockOut,
        ?string $breakStart = null,
        ?string $breakEnd = null
    ): int {
        if (empty($clockIn) || empty($clockOut)) {
            return 0;
        }

        $date = $workDate instanceof \DateTimeInterface
            ? Carbon::instance($workDate)->format('Y-m-d')
            : Carbon::parse($workDate)->format('Y-m-d');

        $start = $this->parseDateTime($date, $clockIn);
        $end = $this->parseDateTime($date, $clockOut);
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $nightStart = Carbon::createFromFormat('Y-m-d H:i', $date . ' 22:00');
        $nightEnd = Carbon::createFromFormat('Y-m-d H:i', $date . ' 06:00')->addDay();

        $nightMinutes = $this->overlapMinutes($start, $end, $nightStart, $nightEnd);

        if (!empty($breakStart) && !empty($breakEnd)) {
            $bStart = $this->parseDateTime($date, $breakStart);
            $bEnd = $this->parseDateTime($date, $breakEnd);
            if ($bEnd->lessThan($bStart)) {
                $bEnd->addDay();
            }
            $breakMinutes = $this->overlapMinutes($bStart, $bEnd, $nightStart, $nightEnd);
            $nightMinutes -= $breakMinutes;
        }

        return max(0, (int) $nightMinutes);
    }

    private function parseDateTime(string $date, string $time): Carbon
    {
        $format = substr_count($time, ':') === 2 ? 'Y-m-d H:i:s' : 'Y-m-d H:i';
        return Carbon::createFromFormat($format, $date . ' ' . $time);
    }

    private function overlapMinutes(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): int
    {
        $start = $aStart->greaterThan($bStart) ? $aStart : $bStart;
        $end = $aEnd->lessThan($bEnd) ? $aEnd : $bEnd;
        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }
        return (int) round($end->diffInSeconds($start) / 60);
    }
}
