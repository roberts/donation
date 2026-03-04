<?php

use App\Enums\DonationStatus;
use App\Enums\FilingStatus;
use App\Filament\Resources\Donations\Pages\EditDonation;
use App\Filament\Resources\Donations\Pages\ListDonations;
use App\Filament\Resources\Donations\Pages\ViewDonation;
use App\Mail\DonationReceipt;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\School;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
    $this->school = School::factory()->create();
});

describe('Donation Resource', function () {
    it('can render index page', function () {
        livewire(ListDonations::class)
            ->assertOk();
    });

    it('can list donations', function () {
        $donations = Donation::factory()->count(5)->create([
            'school_id' => $this->school->id,
        ]);

        livewire(ListDonations::class)
            ->assertCanSeeTableRecords($donations);
    });

    it('can render view page', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
        ]);

        livewire(ViewDonation::class, ['record' => $donation->id])
            ->assertOk();
    });

    it('can render edit page', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
        ]);

        livewire(EditDonation::class, ['record' => $donation->id])
            ->assertOk();
    });

    it('displays correct donation data on view page', function () {
        $donor = Donor::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
            'amount' => 25000,
        ]);

        livewire(ViewDonation::class, ['record' => $donation->id])
            ->assertFormSet([
                'amount' => '250.00',
                'donor_id' => $donor->id,
            ]);
    });

    it('can update donation', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $newDonor = Donor::factory()->create([
            'email' => 'updated@example.com',
        ]);

        livewire(EditDonation::class, ['record' => $donation->id])
            ->fillForm([
                'donor_id' => $newDonor->id,
                'status' => DonationStatus::Paid->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $donation->refresh();

        expect($donation->donor->email)->toBe('updated@example.com');
    });

    it('shows amount as money in table', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'amount' => 10000, // $100.00
        ]);

        livewire(ListDonations::class)
            ->assertCanSeeTableRecords([$donation]);
    });

    it('can search donations by donor name', function () {
        $donor = Donor::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Smith',
        ]);

        $matchingDonation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $donor->id,
        ]);

        $otherDonor = Donor::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $nonMatchingDonation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'donor_id' => $otherDonor->id,
        ]);

        livewire(ListDonations::class)
            ->searchTable('John')
            ->assertCanSeeTableRecords([$matchingDonation])
            ->assertCanNotSeeTableRecords([$nonMatchingDonation]);
    });

    it('can filter donations by filing year', function () {
        $donation2024 = Donation::factory()->create([
            'school_id' => $this->school->id,
            'filing_year' => 2024,
        ]);
        $donation2025 = Donation::factory()->create([
            'school_id' => $this->school->id,
            'filing_year' => 2025,
        ]);

        livewire(ListDonations::class)
            ->filterTable('filing_year', 2024)
            ->assertCanSeeTableRecords([$donation2024])
            ->assertCanNotSeeTableRecords([$donation2025]);
    });

    it('can filter donations by filing status', function () {
        $singleDonation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'filing_status' => FilingStatus::Single,
        ]);
        $marriedDonation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'filing_status' => FilingStatus::MarriedFilingJointly,
        ]);

        livewire(ListDonations::class)
            ->filterTable('filing_status', FilingStatus::Single->value)
            ->assertCanSeeTableRecords([$singleDonation])
            ->assertCanNotSeeTableRecords([$marriedDonation]);
    });

    it('can filter donations by receipt pending', function () {
        $pendingDonation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'receipt_sent_at' => null,
        ]);
        $sentDonation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'receipt_sent_at' => now(),
        ]);

        livewire(ListDonations::class)
            ->filterTable('receipt_pending', true)
            ->assertCanSeeTableRecords([$pendingDonation])
            ->assertCanNotSeeTableRecords([$sentDonation]);
    });

    it('sorts by created_at descending by default', function () {
        $oldDonation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'created_at' => now()->subDays(5),
        ]);
        $newDonation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'created_at' => now(),
        ]);

        livewire(ListDonations::class)
            ->assertCanSeeTableRecords([$newDonation, $oldDonation], inOrder: true);
    });

    it('can view receipt', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
        ]);

        livewire(ListDonations::class)
            ->assertTableActionExists('viewReceipt');
    });

    it('can email receipt', function () {
        Mail::fake();

        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'receipt_sent_at' => null,
        ]);

        livewire(ListDonations::class)
            ->mountTableAction('emailReceipt', $donation)
            ->callMountedTableAction()
            ->assertNotified('Receipt Sent');

        Mail::assertQueued(DonationReceipt::class);

        expect($donation->refresh()->receipt_sent_at)->not->toBeNull();
    });
});
