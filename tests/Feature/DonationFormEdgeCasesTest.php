<?php

use App\Actions\Donation\ProcessDonation;
use App\Enums\FilingStatus;
use App\Livewire\DonationForm;
use App\Models\School;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Donation Form Edge Cases', function () {

    it('enforces rate limiting on submission', function () {
        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(true);

        Livewire::test(DonationForm::class)
            ->call('submit', 'pm_test')
            ->assertHasErrors(['payment' => 'Too many attempts. Please try again later.']);
    });

    it('prevents concurrent submissions using atomic locks', function () {
        Cache::shouldReceive('lock')->andReturnSelf();
        Cache::shouldReceive('get')->andReturn(false); // Lock not acquired

        Livewire::test(DonationForm::class)
            ->call('submit', 'pm_test')
            ->assertHasErrors(['payment' => 'A transaction is already being processed. Please wait.']);
    });

    it('handles payment processing exceptions gracefully', function () {
        $school = School::factory()->create();

        // Let's mock the action to throw an exception
        $this->mock(ProcessDonation::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \Exception('Stripe Error'));
        });

        // Bypass rate limiter and lock for this test
        RateLimiter::shouldReceive('tooManyAttempts')->andReturn(false);
        RateLimiter::shouldReceive('hit')->andReturn(null);

        $lockMock = Mockery::mock(Lock::class);
        $lockMock->shouldReceive('get')->andReturn(true);
        $lockMock->shouldReceive('release')->andReturn(true);
        Cache::shouldReceive('lock')->andReturn($lockMock);

        Livewire::test(DonationForm::class)
            ->set('form.filingStatus', FilingStatus::Single->value)
            // Set other required fields to pass validation
            ->set('form.phone', '(555) 555-5555') // Valid format
            ->set('form.address', '123 St')
            ->set('form.city', 'City')
            ->set('form.state', 'AZ')
            ->set('form.zip', '85000')
            ->set('form.email', 'test@test.com')
            ->set('form.email_confirmation', 'test@test.com')
            ->set('form.donors.0.first_name', 'Test')
            ->set('form.donors.0.last_name', 'User')
            ->set('form.filingYear', '2025')
            ->set('form.boolQCO', 'no')
            ->set('form.totalAmount', '100')
            ->set('form.schoolId', $school->id)
            ->set('form.paymentMethodId', 'pm_test')
            ->call('submit', 'pm_test')
            ->assertHasErrors(['payment']);
    });

    it('validates step 1 correctly', function () {
        $component = Livewire::test(DonationForm::class);

        // Empty form
        expect($component->instance()->isStep1Valid())->toBeFalse();

        // Fill partial
        $component->set('form.filingStatus', FilingStatus::Single->value);
        expect($component->instance()->isStep1Valid())->toBeFalse();

        // Fill all
        $component->set('form.phone', '555-555-5555')
            ->set('form.address', '123 St')
            ->set('form.city', 'City')
            ->set('form.state', 'AZ')
            ->set('form.zip', '85000')
            ->set('form.email', 'test@test.com')
            ->set('form.email_confirmation', 'test@test.com')
            ->set('form.donors.0.first_name', 'Test')
            ->set('form.donors.0.last_name', 'User');

        expect($component->instance()->isStep1Valid())->toBeTrue();
    });

    it('validates step 2 correctly', function () {
        $component = Livewire::test(DonationForm::class);

        expect($component->instance()->isStep2Valid())->toBeFalse();

        $component->set('form.filingYear', '2025');
        expect($component->instance()->isStep2Valid())->toBeFalse();

        $component->set('form.boolQCO', 'no');
        expect($component->instance()->isStep2Valid())->toBeTrue();
    });

    it('validates step 3 correctly', function () {
        $component = Livewire::test(DonationForm::class);

        expect($component->instance()->isStep3Valid())->toBeFalse();

        $component->set('form.totalAmount', '100');
        expect($component->instance()->isStep3Valid())->toBeTrue();
    });

    it('handles school search with empty query', function () {
        School::factory()->count(5)->create();

        $component = Livewire::test(DonationForm::class);

        // Should return empty collection or default list depending on implementation
        // Based on previous code reading, it returns empty collection if query is empty
        // BUT I changed it to return 10 results if empty in a previous turn.
        // Let's verify that behavior.

        $results = $component->instance()->searchSchools('');
        expect($results)->toHaveCount(5);
    });

    it('handles school search with no matches', function () {
        School::factory()->create(['name' => 'Alpha']);

        $component = Livewire::test(DonationForm::class);

        $results = $component->instance()->searchSchools('Omega');
        expect($results)->toBeEmpty();
    });

    it('updates step1Valid property on update', function () {
        $component = Livewire::test(DonationForm::class);

        expect($component->get('step1Valid'))->toBeFalse();

        // Fill form to make it valid
        $component->set('form.filingStatus', FilingStatus::Single->value)
            ->set('form.phone', '555-555-5555')
            ->set('form.address', '123 St')
            ->set('form.city', 'City')
            ->set('form.state', 'AZ')
            ->set('form.zip', '85000')
            ->set('form.email', 'test@test.com')
            ->set('form.email_confirmation', 'test@test.com')
            ->set('form.donors.0.first_name', 'Test')
            ->set('form.donors.0.last_name', 'User');

        // The updated hook should fire and update the property
        expect($component->get('step1Valid'))->toBeTrue();
    });
});
