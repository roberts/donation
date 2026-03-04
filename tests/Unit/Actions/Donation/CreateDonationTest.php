<?php

use App\Actions\Donation\CreateDonation;
use App\Data\AddressData;
use App\Data\DonationFormData;
use App\Enums\DonationStatus;
use App\Enums\FilingStatus;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CreateDonation Action', function () {
    it('creates a donation', function () {
        $school = School::factory()->create();

        $donor = Donor::create([
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $data = new DonationFormData(
            donor_email: 'test@example.com',
            donor_first_name: 'John',
            donor_last_name: 'Doe',
            donor_title: 'Mr.',
            donor_phone: '1234567890',
            mailing_address: new AddressData('123 Main St', null, 'City', 'State', '12345', 'US'),
            billing_address: new AddressData('123 Main St', null, 'City', 'State', '12345', 'US'),
            school_id: $school->id,
            custom_school: null,
            amount: 1000,
            filing_year: 2025,
            filing_status: FilingStatus::Single,
            qco: null,
            payment_method_id: 'pm_123'
        );

        $action = new CreateDonation;
        $donation = $action->execute($data, $donor);

        expect($donation)->toBeInstanceOf(Donation::class)
            ->amount->toBe(1000)
            ->donor_id->toBe($donor->id)
            ->status->toBe(DonationStatus::Pending);
    });
});
