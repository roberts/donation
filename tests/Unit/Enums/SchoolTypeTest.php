<?php

use App\Enums\SchoolType;

describe('SchoolType Enum', function () {
    it('returns correct labels', function () {
        expect(SchoolType::Private->getLabel())->toBe('Private')
            ->and(SchoolType::Public->getLabel())->toBe('Public')
            ->and(SchoolType::Charter->getLabel())->toBe('Charter');
    });

    it('returns correct colors', function () {
        expect(SchoolType::Private->getColor())->toBe('danger')
            ->and(SchoolType::Public->getColor())->toBe('success')
            ->and(SchoolType::Charter->getColor())->toBe('info');
    });
});
