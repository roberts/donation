<?php

declare(strict_types=1);

namespace App\Actions\Donation;

use App\Actions\Donor\CreateOrUpdateDonor;
use App\Contracts\PaymentGateway;
use App\Data\DonationFormData;
use App\Data\PaymentData;
use App\Enums\DonationStatus;
use App\Enums\TransactionStatus;
use App\Events\DonationProcessed;
use App\Models\Donation;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;

class ProcessDonation
{
    public function __construct(
        protected PaymentGateway $paymentGateway,
        protected CreateOrUpdateDonor $createOrUpdateDonor,
        protected CreateDonation $createDonation
    ) {}

    /**
     * @throws Exception
     */
    public function execute(DonationFormData $data): Donation
    {
        return DB::transaction(function () use ($data) {
            // 1. Create or Update Donor
            $donor = $this->createOrUpdateDonor->execute($data);

            // 2. Create Donation
            $donation = $this->createDonation->execute($data, $donor);

            // Process Payment
            $paymentResult = $this->paymentGateway->charge(new PaymentData(
                amount: $donation->amount,
                token: $data->payment_method_id,
                description: 'Donation to IBE Foundation',
                metadata: [
                    'donation_id' => (string) $donation->id,
                    'donor_email' => $donor->email,
                ]
            ));

            $donation->update([
                'status' => DonationStatus::Paid,
            ]);

            Transaction::create([
                'donation_id' => $donation->id,
                'payment_intent_id' => $paymentResult->id,
                'amount' => $donation->amount,
                'status' => TransactionStatus::Succeeded,
                'livemode' => $paymentResult->livemode,
                'payload' => json_encode($paymentResult->originalResponse),
            ]);

            // Dispatch Event
            DonationProcessed::dispatch($donation);

            return $donation;
        });
    }
}
