<?php

use App\Enums\TransactionStatus;
use App\Models\Transaction;

describe('Transaction Model', function () {
    it('identifies successful transactions', function () {
        $transaction = Transaction::factory()->make(['status' => TransactionStatus::Succeeded]);
        expect($transaction->isSuccessful())->toBeTrue();
    });

    it('identifies unsuccessful transactions', function () {
        $statuses = [
            TransactionStatus::Pending,
            TransactionStatus::Processing,
            TransactionStatus::Failed,
            TransactionStatus::Canceled,
            TransactionStatus::Refunded,
            TransactionStatus::RequiresPaymentMethod,
        ];

        foreach ($statuses as $status) {
            $transaction = Transaction::factory()->make(['status' => $status]);
            expect($transaction->isSuccessful())->toBeFalse();
        }
    });
});
