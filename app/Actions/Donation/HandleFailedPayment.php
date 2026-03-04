<?php

namespace App\Actions\Donation;

use App\Enums\DonationStatus;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

class HandleFailedPayment
{
    public function execute(PaymentIntent $paymentIntent): void
    {
        $failureMessage = $paymentIntent->last_payment_error !== null
            ? ($paymentIntent->last_payment_error->message ?? 'Unknown error')
            : 'Unknown error';

        Log::warning('Stripe webhook: payment_intent.payment_failed', [
            'payment_intent_id' => $paymentIntent->id,
            'failure_message' => $failureMessage,
        ]);

        $transaction = Transaction::where('payment_intent_id', $paymentIntent->id)->first();

        if (! $transaction) {
            return;
        }

        $transaction->update([
            'status' => TransactionStatus::Failed,
            'payload' => $paymentIntent->toArray(),
        ]);

        if ($transaction->donation) {
            $transaction->donation->update(['status' => DonationStatus::Failed]);
        }
    }
}
