<?php

use App\Services\FilingYearService;
use Illuminate\Support\Carbon;

describe('FilingYearService', function () {
    it('returns current year and prior year before April 15', function () {
        Carbon::setTestNow('2025-04-14');

        $service = new FilingYearService;
        $years = $service->getAvailableFilingYears();

        expect($years)->toBe([2025, 2024]);
    });

    it('returns only current year after April 15', function () {
        Carbon::setTestNow('2025-04-16');

        $service = new FilingYearService;
        $years = $service->getAvailableFilingYears();

        expect($years)->toBe([2025]);
    });

    it('returns correct limits for a given year', function () {
        config(['tax-credits.limits.2025' => ['single' => 500, 'married' => 1000]]);

        $service = new FilingYearService;
        $limits = $service->getLimits(2025);

        expect($limits)->toBe(['single' => 500, 'married' => 1000]);
    });

    it('returns null for unknown year limits', function () {
        $service = new FilingYearService;
        $limits = $service->getLimits(1990);

        expect($limits)->toBeNull();
    });
});
