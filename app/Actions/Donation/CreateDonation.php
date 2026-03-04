<?php

namespace App\Actions\Donation;

use App\Data\DonationFormData;
use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Models\Donation;
use App\Models\Donor;

class CreateDonation
{
    public function execute(DonationFormData $data, Donor $donor): Donation
    {
        return Donation::create([
            'school_id' => $data->school_id,
            'donor_id' => $donor->id,
            'amount' => $data->amount,
            'status' => DonationStatus::Pending, // Will be updated to succeeded after payment
            'payment_method' => PaymentMethod::Card,
            'filing_year' => $data->filing_year,
            'filing_status' => $data->filing_status,
            'qco' => $data->qco,
            'school_name_snapshot' => $data->school_name_snapshot ?? null,
            'tax_professional_name' => $data->tax_professional_name ?? null,
            'tax_professional_phone' => $data->tax_professional_phone ?? null,
            'tax_professional_email' => $data->tax_professional_email ?? null,
        ]);
    }
}
