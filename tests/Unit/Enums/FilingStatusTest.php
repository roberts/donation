<?php

use App\Enums\FilingStatus;

describe('FilingStatus Enum', function () {
    it('returns correct labels', function () {
        expect(FilingStatus::Single->label())->toBe('Single')
            ->and(FilingStatus::MarriedFilingSeparately->label())->toBe('Married Filing Separately')
            ->and(FilingStatus::MarriedFilingJointly->label())->toBe('Married Filing Jointly');
    });
});
