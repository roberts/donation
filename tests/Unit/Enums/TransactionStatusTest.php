<?php

use App\Enums\TransactionStatus;

describe('TransactionStatus Enum', function () {
    it('returns correct labels', function () {
        expect(TransactionStatus::Succeeded->getLabel())->toBe('Succeeded')
            ->and(TransactionStatus::Pending->getLabel())->toBe('Pending')
            ->and(TransactionStatus::Failed->getLabel())->toBe('Failed');
    });

    it('returns correct colors', function () {
        expect(TransactionStatus::Succeeded->getColor())->toBe('success')
            ->and(TransactionStatus::Pending->getColor())->toBe('warning')
            ->and(TransactionStatus::Failed->getColor())->toBe('danger')
            ->and(TransactionStatus::Refunded->getColor())->toBe('gray');
    });

    it('identifies successful status', function () {
        expect(TransactionStatus::Succeeded->isSuccessful())->toBeTrue()
            ->and(TransactionStatus::Pending->isSuccessful())->toBeFalse();
    });
});
