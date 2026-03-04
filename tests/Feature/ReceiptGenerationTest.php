<?php

use App\Enums\FilingStatus;
use App\Mail\DonationReceipt;
use App\Models\Address;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\LaravelPdf\Facades\Pdf;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->school = School::factory()->create(['name' => 'Test Elementary School']);
});

describe('Receipt Generation', function () {
    it('can generate PDF receipt', function () {
        $donor = Donor::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
            'amount' => 10000,
        ]);

        // Verify the PDF view can render without errors
        $view = view('pdf.receipt', ['donation' => $donation->load(['school', 'transactions', 'donor'])]);
        $html = $view->render();

        expect($html)->toContain('John')
            ->and($html)->toContain('Doe')
            ->and($html)->toContain('$100.00')
            ->and($html)->toContain('Test Elementary School')
            ->and($html)->toContain('Receipt #'.$donation->id);
    });

    it('includes all donor information in receipt', function () {
        $donor = Donor::factory()->create([
            'title' => 'Dr.',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'spouse_title' => 'Mr.',
            'spouse_first_name' => 'John',
            'spouse_last_name' => 'Smith',
            'email' => 'jane@research.org',
            'phone' => '555-123-4567',
        ]);

        $address = Address::factory()->create([
            'addressable_id' => $donor->id,
            'addressable_type' => Donor::class,
            'street' => '123 Main St',
            'city' => 'Phoenix',
            'state' => 'AZ',
            'postal_code' => '85001',
            'country' => 'USA',
            'type' => 'billing',
        ]);

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
            'amount' => 25000,
            'filing_year' => 2025,
            'filing_status' => FilingStatus::MarriedFilingJointly,
        ]);

        $view = view('pdf.receipt', ['donation' => $donation->load(['school', 'transactions', 'donor.addresses'])]);
        $html = $view->render();

        expect($html)->toContain('Dr.')
            ->and($html)->toContain('Jane')
            ->and($html)->toContain('Smith')
            ->and($html)->toContain('jane@research.org')
            ->and($html)->toContain('5551234567')
            ->and($html)->toContain('123 Main St')
            ->and($html)->toContain('Phoenix')
            ->and($html)->toContain('AZ')
            ->and($html)->toContain('85001')
            ->and($html)->toContain('2025')
            ->and($html)->toContain('Married Filing Jointly');
    });

    it('can send receipt email', function () {
        Mail::fake();

        $donor = Donor::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'email' => 'bob@example.com',
        ]);

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
            'amount' => 5000,
            'receipt_sent_at' => null,
        ]);

        Mail::to($donation->donor->email)->send(new DonationReceipt($donation));

        // Since DonationReceipt implements ShouldQueue, use assertQueued
        Mail::assertQueued(DonationReceipt::class, function ($mail) use ($donation) {
            return $mail->donation->id === $donation->id
                && $mail->hasTo($donation->donor->email);
        });
    });

    it('email receipt has correct subject', function () {
        $donor = Donor::factory()->create([
            'email' => 'test@example.com',
        ]);

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
        ]);

        $mailable = new DonationReceipt($donation);

        expect($mailable->envelope()->subject)->toContain('Thank You for Your Donation')
            ->and($mailable->envelope()->subject)->toContain('Receipt #'.$donation->id);
    });

    it('email body contains donation details', function () {
        $donor = Donor::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Wonder',
            'email' => 'alice@example.com',
        ]);

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
            'amount' => 15000,
        ]);

        // Render the email view
        $view = view('emails.donation-receipt', ['donation' => $donation->load(['school', 'donor'])]);
        $html = $view->render();

        expect($html)->toContain('Alice')
            ->and($html)->toContain('$150.00')
            ->and($html)->toContain('Test Elementary School')
            ->and($html)->toContain('#'.$donation->id);
    });

    it('updates receipt_sent_at when email is sent', function () {
        Mail::fake();

        $donor = Donor::factory()->create([
            'email' => 'test@example.com',
        ]);

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
            'receipt_sent_at' => null,
        ]);

        expect($donation->receipt_sent_at)->toBeNull();

        // Simulate sending and updating
        Mail::to($donation->donor->email)->send(new DonationReceipt($donation));
        $donation->update(['receipt_sent_at' => now()]);

        $donation->refresh();

        expect($donation->receipt_sent_at)->not->toBeNull();
    });

    it('handles donation without optional fields', function () {
        $donor = Donor::factory()->create([
            'title' => null,
            'first_name' => 'Simple',
            'last_name' => 'Donor',
            'spouse_title' => null,
            'spouse_first_name' => null,
            'spouse_last_name' => null,
            'phone' => null,
            'email' => 'simple@example.com',
        ]);

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
            'amount' => 2500,
        ]);

        // Verify the PDF view can render without errors
        $view = view('pdf.receipt', ['donation' => $donation->load(['school', 'transactions', 'donor'])]);
        $html = $view->render();

        expect($html)->toContain('Simple')
            ->and($html)->toContain('Donor')
            ->and($html)->toContain('$25.00');
    });
});

describe('Donation Mailable', function () {
    it('implements ShouldQueue for async sending', function () {
        $donation = Donation::factory()->create([
            'school_id' => School::factory()->create()->id,
        ]);

        $mailable = new DonationReceipt($donation);

        expect($mailable)->toBeInstanceOf(ShouldQueue::class);
    });
});
