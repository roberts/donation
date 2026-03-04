<?php

use App\Models\Address;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\School;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Donation Model', function () {
    it('correctly formats donor name for single donor', function () {
        $donor = Donor::factory()->create([
            'title' => 'Mr.',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'spouse_first_name' => null,
        ]);
        $donation = Donation::factory()->create(['donor_id' => $donor->id]);

        expect($donation->donor_name)->toBe('Mr. John Doe');
    });

    it('correctly formats donor name for couple', function () {
        $donor = Donor::factory()->create([
            'title' => 'Mr.',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'spouse_title' => 'Mrs.',
            'spouse_first_name' => 'Jane',
            'spouse_last_name' => 'Doe',
        ]);
        $donation = Donation::factory()->create(['donor_id' => $donor->id]);

        expect($donation->donor_name)->toBe('Mr. John Doe & Mrs. Jane Doe');
    });

    it('handles unknown donor for name', function () {
        $donor = Donor::factory()->create();
        $donation = Donation::factory()->create(['donor_id' => $donor->id]);

        $donor->delete();
        $donation->refresh();

        // By default, belongsTo will return null if the related model is soft deleted
        expect($donation->donor)->toBeNull()
            ->and($donation->donor_name)->toBe('Unknown Donor');
    });

    it('formats mailing address correctly', function () {
        $donor = Donor::factory()->create();
        Address::factory()->create([
            'addressable_id' => $donor->id,
            'addressable_type' => Donor::class,
            'type' => 'mailing',
            'street' => '123 Main St',
            'street_line_2' => 'Apt 4B',
            'city' => 'Phoenix',
            'state' => 'AZ',
            'postal_code' => '85001',
            'country' => 'US',
        ]);
        $donation = Donation::factory()->create(['donor_id' => $donor->id]);

        $expected = "123 Main St\nApt 4B\nPhoenix, AZ 85001";
        expect($donation->formatted_mailing_address)->toBe($expected);
    });

    it('formats international mailing address correctly', function () {
        $donor = Donor::factory()->create();
        Address::factory()->create([
            'addressable_id' => $donor->id,
            'addressable_type' => Donor::class,
            'type' => 'mailing',
            'street' => '10 Downing St',
            'street_line_2' => null,
            'city' => 'London',
            'state' => 'UK',
            'postal_code' => 'SW1A 2AA',
            'country' => 'United Kingdom',
        ]);
        $donation = Donation::factory()->create(['donor_id' => $donor->id]);

        $expected = "10 Downing St\nLondon, UK SW1A 2AA\nUnited Kingdom";
        expect($donation->formatted_mailing_address)->toBe($expected);
    });

    it('calculates amount in dollars', function () {
        $donation = Donation::factory()->make(['amount' => 10050]);
        expect($donation->amount_dollars)->toBe('100.50');

        $donation = Donation::factory()->make(['amount' => 500]);
        expect($donation->amount_dollars)->toBe('5.00');
    });

    it('belongs to a school', function () {
        $school = School::factory()->create();
        $donation = Donation::factory()->create(['school_id' => $school->id]);

        expect($donation->school)->toBeInstanceOf(School::class)
            ->and($donation->school->id)->toBe($school->id);
    });

    it('belongs to a donor', function () {
        $donor = Donor::factory()->create();
        $donation = Donation::factory()->create(['donor_id' => $donor->id]);

        expect($donation->donor)->toBeInstanceOf(Donor::class)
            ->and($donation->donor->id)->toBe($donor->id);
    });

    it('has many transactions', function () {
        $donation = Donation::factory()->create();
        $transaction = Transaction::factory()->create(['donation_id' => $donation->id]);

        expect($donation->transactions)->toHaveCount(1)
            ->and($donation->transactions->first()->id)->toBe($transaction->id);
    });
});
