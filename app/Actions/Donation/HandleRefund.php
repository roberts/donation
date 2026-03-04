<?php

namespace App\Actions\Donation;

use App\Enums\DonationStatus;
use App\Enums\TransactionStatus;
use App\Models\Donation;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;

class HandleRefund
{
    public function execute(Charge $charge): void
    {
        Log::info('Stripe webhook: charge.refunded', [
            'charge_id' => $charge->id,
            'payment_intent_id' => $charge->payment_intent,
        ]);

        // Find the donation by payment intent ID
        $paymentIntentId = is_string($charge->payment_intent) ? $charge->payment_intent : null;

        if (! $paymentIntentId) {
            return;
        }

        $transaction = Transaction::where('payment_intent_id', $paymentIntentId)->first();

        if (! $transaction) {
            Log::warning('Refund received for unknown transaction', ['payment_intent_id' => $paymentIntentId]);

            return;
        }

        $donation = $transaction->donation;

        if ($donation) {
            // Create a transaction record for the refund
            Transaction::create([
                'donation_id' => $donation->id,
                'payment_intent_id' => $paymentIntentId,
                'amount' => $charge->amount_refunded,
                'status' => TransactionStatus::Refunded,
                'livemode' => $charge->livemode,
                'payload' => $charge->toArray(),
            ]);

            $donation->update(['status' => DonationStatus::Refunded]);
        }
    }
}
