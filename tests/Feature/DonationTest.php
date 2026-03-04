<?php

use App\Enums\SchoolType;
use App\Livewire\DonationForm;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create([
        'name' => 'Test Private School',
        'type' => SchoolType::Private,
    ]);
});

describe('Donation Form Component', function () {
    it('renders successfully', function () {
        Livewire::test(DonationForm::class)
            ->assertStatus(200);
    });

    it('allows preselecting a school via query parameter', function () {
        Livewire::withQueryParams(['schoolId' => $this->school->id])
            ->test(DonationForm::class)
            ->assertSet('form.schoolId', (string) $this->school->id)
            ->assertSet('selectedSchool.id', $this->school->id);
    });
});

describe('Donation Form Validation', function () {
    it('validates required fields', function () {
        Livewire::test(DonationForm::class)
            ->call('submit', 'pm_test_123')
            ->assertHasErrors([
                'form.filingStatus',
                'form.phone',
                'form.address',
                'form.city',
                'form.state',
                'form.zip',
                'form.email',
                'form.email_confirmation',
                'form.filingYear',
                'form.boolQCO',
                'form.totalAmount',
            ]);
    });

    it('validates minimum donation amount', function () {
        Livewire::test(DonationForm::class)
            ->set('form.totalAmount', 4)
            ->call('submit', 'pm_test_123')
            ->assertHasErrors(['form.totalAmount']);
    });

    it('validates email format', function () {
        Livewire::test(DonationForm::class)
            ->set('form.email', 'not-an-email')
            ->call('submit', 'pm_test_123')
            ->assertHasErrors(['form.email']);
    });

    it('validates email confirmation matches', function () {
        Livewire::test(DonationForm::class)
            ->set('form.email', 'john@example.com')
            ->set('form.email_confirmation', 'jane@example.com')
            ->call('submit', 'pm_test_123')
            ->assertHasErrors(['form.email_confirmation']);
    });
});

describe('Donation Success Page', function () {
    it('displays donation confirmation', function () {
        $response = $this->get(route('donation.success'));

        $response->assertStatus(200)
            ->assertViewIs('donation.success');
    });
});
