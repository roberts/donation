<?php

declare(strict_types=1);

namespace App\Actions\Donor;

use App\Data\DonationFormData;
use App\Models\Donor;

class CreateOrUpdateDonor
{
    public function __construct(
        protected CreateUserForDonor $createUserForDonor
    ) {}

    public function execute(DonationFormData $data): Donor
    {
        // 1. Create or Update Donor
        $donor = Donor::updateOrCreate(
            ['email' => $data->donor_email],
            [
                'first_name' => $data->donor_first_name,
                'last_name' => $data->donor_last_name,
                'title' => $data->donor_title,
                'spouse_title' => $data->donor_spouse_title,
                'spouse_first_name' => $data->donor_spouse_first_name,
                'spouse_last_name' => $data->donor_spouse_last_name,
                'phone' => $data->donor_phone,
            ]
        );

        // 1b. Create User for Donor if not exists
        $this->createUserForDonor->execute($donor);

        // 2. Create or Update Addresses
        if ($data->mailing_address) {
            $donor->addresses()->updateOrCreate(
                ['type' => 'mailing'],
                [
                    'street' => $data->mailing_address->street,
                    'street_line_2' => $data->mailing_address->street_line_2,
                    'city' => $data->mailing_address->city,
                    'state' => $data->mailing_address->state,
                    'postal_code' => $data->mailing_address->postal_code,
                    'country' => $data->mailing_address->country,
                ]
            );
        }

        $donor->addresses()->updateOrCreate(
            ['type' => 'billing'],
            [
                'street' => $data->billing_address->street,
                'street_line_2' => $data->billing_address->street_line_2,
                'city' => $data->billing_address->city,
                'state' => $data->billing_address->state,
                'postal_code' => $data->billing_address->postal_code,
                'country' => $data->billing_address->country,
            ]
        );

        return $donor;
    }
}
