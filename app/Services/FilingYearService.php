<?php

namespace App\Services;

class FilingYearService
{
    /**
     * Get available filing years based on current date.
     * Prior year is available until April 15th tax deadline.
     *
     * @return array<int, int>
     */
    public function getAvailableFilingYears(): array
    {
        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;
        $currentDay = (int) now()->day;

        // Prior year is available until April 15
        $priorYearAvailable = $currentMonth < 4 || ($currentMonth === 4 && $currentDay <= 15);

        $years = [$currentYear];

        if ($priorYearAvailable) {
            $years[] = $currentYear - 1;
        }

        // Sort descending (current year first)
        rsort($years);

        return $years;
    }

    /**
     * Get contribution limits for a specific year.
     *
     * @return array{single: int, married: int}|null
     */
    public function getLimits(int $year): ?array
    {
        return config("tax-credits.limits.{$year}");
    }
}
