<?php

use App\Actions\Donor\CreateOrUpdateDonor;
use App\Data\AddressData;
use App\Data\DonationFormData;
use App\Enums\FilingStatus;
use App\Models\Donor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

describe('CreateOrUpdateDonor Action', function () {
    it('creates a new donor and user', function () {
        Role::create(['name' => 'donor']);

        $data = new DonationFormData(
            donor_email: 'test@example.com',
            donor_first_name: 'John',
            donor_last_name: 'Doe',
            donor_title: 'Mr.',
            donor_phone: '1234567890',
            mailing_address: new AddressData('123 Main St', null, 'City', 'State', '12345', 'US'),
            billing_address: new AddressData('123 Main St', null, 'City', 'State', '12345', 'US'),
            school_id: 1,
            custom_school: null,
            amount: 1000,
            filing_year: 2025,
            filing_status: FilingStatus::Single,
            qco: null,
            payment_method_id: 'pm_123'
        );

        $action = app(CreateOrUpdateDonor::class);
        $donor = $action->execute($data);

        expect($donor)->toBeInstanceOf(Donor::class)
            ->email->toBe('test@example.com')
            ->first_name->toBe('John')
            ->last_name->toBe('Doe');

        expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
        expect($donor->user_id)->not->toBeNull();
        expect($donor->addresses)->toHaveCount(2);
    });

    it('updates existing donor', function () {
        Role::create(['name' => 'donor']);

        $existingDonor = Donor::create([
            'email' => 'test@example.com',
            'first_name' => 'Old',
            'last_name' => 'Name',
        ]);

        $data = new DonationFormData(
            donor_email: 'test@example.com',
            donor_first_name: 'John',
            donor_last_name: 'Doe',
            donor_title: 'Mr.',
            donor_phone: '1234567890',
            mailing_address: new AddressData('123 Main St', null, 'City', 'State', '12345', 'US'),
            billing_address: new AddressData('123 Main St', null, 'City', 'State', '12345', 'US'),
            school_id: 1,
            custom_school: null,
            amount: 1000,
            filing_year: 2025,
            filing_status: FilingStatus::Single,
            qco: null,
            payment_method_id: 'pm_123'
        );

        $action = app(CreateOrUpdateDonor::class);
        $donor = $action->execute($data);

        expect($donor->id)->toBe($existingDonor->id);
        expect($donor->first_name)->toBe('John');
    });
});
