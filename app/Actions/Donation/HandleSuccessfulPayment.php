<?php

namespace App\Actions\Donation;

use App\Enums\DonationStatus;
use App\Enums\FilingStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

class HandleSuccessfulPayment
{
    public function execute(PaymentIntent $paymentIntent): void
    {
        Log::info('Stripe webhook: payment_intent.succeeded', [
            'payment_intent_id' => $paymentIntent->id,
        ]);

        // Find the transaction
        $transaction = Transaction::where('payment_intent_id', $paymentIntent->id)->first();

        if ($transaction) {
            $transaction->update([
                'status' => TransactionStatus::Succeeded,
                'payload' => $paymentIntent->toArray(),
            ]);

            if ($transaction->donation) {
                $transaction->donation->update(['status' => DonationStatus::Paid]);
            }

            return;
        }

        // If transaction doesn't exist, we may need to create it from webhook metadata
        $this->createDonationFromWebhook($paymentIntent);
    }

    protected function createDonationFromWebhook(PaymentIntent $paymentIntent): ?Donation
    {
        $metadata = $paymentIntent->metadata;

        // If we don't have the school_id in metadata, we can't create the donation
        if (empty($metadata->school_id)) {
            Log::warning('Cannot create donation from webhook: missing school_id', [
                'payment_intent_id' => $paymentIntent->id,
            ]);

            return null;
        }

        // Create or Update Donor
        $donor = Donor::updateOrCreate(
            ['email' => $metadata->donor_email ?? $paymentIntent->receipt_email ?? 'unknown@example.com'],
            [
                'first_name' => 'Webhook',
                'last_name' => 'Donation',
            ]
        );

        // Create Address for Donor
        $donor->addresses()->updateOrCreate(
            ['type' => 'mailing'],
            [
                'street' => 'Unknown',
                'city' => 'Unknown',
                'state' => 'AZ',
                'postal_code' => '00000',
                'country' => 'US',
            ]
        );

        // Create a minimal donation record
        $donation = Donation::create([
            'school_id' => $metadata->school_id,
            'donor_id' => $donor->id,
            'payment_method' => PaymentMethod::Card,
            'amount' => $paymentIntent->amount,
            'status' => DonationStatus::Paid,
            'filing_year' => $metadata->filing_year ?? now()->year,
            'filing_status' => isset($metadata->filing_status) ? (FilingStatus::tryFrom($metadata->filing_status) ?? FilingStatus::Single) : FilingStatus::Single,
            'school_name_snapshot' => $metadata->school_name ?? 'Unknown',
        ]);

        Log::info('Created donation from webhook', [
            'donation_id' => $donation->id,
            'payment_intent_id' => $paymentIntent->id,
        ]);

        // Create the transaction
        Transaction::create([
            'donation_id' => $donation->id,
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'status' => TransactionStatus::tryFrom($paymentIntent->status) ?? TransactionStatus::Succeeded,
            'livemode' => $paymentIntent->livemode,
            'payload' => $paymentIntent->toArray(),
        ]);

        return $donation;
    }
}
