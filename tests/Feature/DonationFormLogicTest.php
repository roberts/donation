<?php

use App\Enums\FilingStatus;
use App\Enums\SchoolType;
use App\Livewire\DonationForm;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create([
        'name' => 'Test Private School',
        'type' => SchoolType::Private,
    ]);
});

describe('Donation Form Logic', function () {
    it('resets donors list when filing status changes to single', function () {
        $component = Livewire::test(DonationForm::class)
            ->set('form.filingStatus', FilingStatus::MarriedFilingJointly->value)
            ->call('addDonor');

        $component->assertCount('form.donors', 2);

        $component->set('form.filingStatus', FilingStatus::Single->value)
            ->assertCount('form.donors', 1);
    });

    it('can add and remove donors', function () {
        $component = Livewire::test(DonationForm::class)
            ->set('form.filingStatus', FilingStatus::MarriedFilingJointly->value);

        $component->call('addDonor')
            ->assertCount('form.donors', 2);

        // Cannot add more than 2
        $component->call('addDonor')
            ->assertCount('form.donors', 2);

        $component->call('removeDonor', 1)
            ->assertCount('form.donors', 1);
    });

    it('can search for schools', function () {
        $school1 = School::factory()->create(['name' => 'Alpha Academy']);
        $school2 = School::factory()->create(['name' => 'Beta High']);

        $component = Livewire::test(DonationForm::class);

        $results = $component->instance()->searchSchools('Alpha');
        expect($results)->toHaveCount(1)
            ->and($results->first()['id'])->toBe($school1->id);

        $results = $component->instance()->searchSchools('High');
        expect($results)->toHaveCount(1)
            ->and($results->first()['id'])->toBe($school2->id);
    });

    it('calculates available years correctly based on date', function () {
        // Date: Jan 1, 2026. Both 2025 (until April) and 2026 (started) should be available.
        Carbon::setTestNow('2026-01-01');

        $component = Livewire::test(DonationForm::class);
        $years = $component->get('availableYears');

        expect($years)->toContain('2025')
            ->toContain('2026');

        // Date: May 1, 2026. 2025 expired. 2026 available.
        Carbon::setTestNow('2026-05-01');

        $component = Livewire::test(DonationForm::class);
        $years = $component->get('availableYears');

        expect($years)->not->toContain('2025')
            ->toContain('2026');

        Carbon::setTestNow(); // Reset
    });

    it('calculates max credit correctly', function () {
        $component = Livewire::test(DonationForm::class)
            ->set('form.filingYear', '2025')
            ->set('form.filingStatus', FilingStatus::Single->value);

        expect($component->get('maxCredit'))->toBe(495);

        $component->set('form.filingStatus', FilingStatus::MarriedFilingJointly->value);
        expect($component->get('maxCredit'))->toBe(987);
    });

    it('calculates max donation amount considering QCO', function () {
        $component = Livewire::test(DonationForm::class)
            ->set('form.filingYear', '2025')
            ->set('form.filingStatus', FilingStatus::Single->value) // Limit 495
            ->set('form.qcoAmount', 100);

        $component->call('calculateMaxDonation');
        // 495 - 100 = 395
        // Note: calculateMaxDonation returns the value, it doesn't set a property unless useMaxDonation is called?
        // Looking at code: calculateMaxDonation returns value. useMaxDonation sets form.totalAmount.

        $max = $component->instance()->calculateMaxDonation();
        expect($max)->toBe(395.0);

        $component->call('useMaxDonation')
            ->assertSet('form.totalAmount', 395);
    });
});
