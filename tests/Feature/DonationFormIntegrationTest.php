<?php

use App\Contracts\PaymentGateway;
use App\Data\PaymentResult;
use App\Enums\DonationStatus;
use App\Enums\FilingStatus;
use App\Enums\SchoolType;
use App\Livewire\DonationForm;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\School;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Stripe\PaymentIntent;

uses(RefreshDatabase::class);

afterEach(function () {
    Mockery::close();
});

beforeEach(function () {
    Mail::fake();
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->school = School::factory()->create([
        'name' => 'Test Private School',
        'type' => SchoolType::Private,
    ]);
});

describe('Donation Form Integration', function () {
    it('creates donation, donor, user, and transaction on successful submission', function () {
        // Mock PaymentGateway
        $paymentGatewayMock = Mockery::mock(PaymentGateway::class);

        $paymentResult = new PaymentResult(
            id: 'pi_test_123',
            amount: 10000,
            status: 'succeeded',
            livemode: false,
            originalResponse: new PaymentIntent('pi_test_123')
        );

        $paymentGatewayMock->shouldReceive('charge')
            ->once()
            ->andReturn($paymentResult);

        $this->app->instance(PaymentGateway::class, $paymentGatewayMock);

        Livewire::test(DonationForm::class)
            ->set('form.schoolId', $this->school->id)
            ->set('form.filingStatus', FilingStatus::Single->value)
            ->set('form.filingYear', 2025)
            ->set('form.donors.0.first_name', 'John')
            ->set('form.donors.0.last_name', 'Doe')
            ->set('form.email', 'john@example.com')
            ->set('form.email_confirmation', 'john@example.com')
            ->set('form.phone', '(555) 123-4567')
            ->set('form.address', '123 Main St')
            ->set('form.city', 'Phoenix')
            ->set('form.state', 'AZ')
            ->set('form.zip', '85001')
            ->set('form.totalAmount', 100)
            ->set('form.boolQCO', 'no')
            ->set('form.captchaToken', 'dummy-token')
            ->call('submit', 'pm_card_test');

        // Assert Donor Created
        $this->assertDatabaseHas('donors', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Assert User Created
        $user = User::where('email', 'john@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->hasRole('donor'))->toBeTrue();
        expect($user->donor->email)->toBe('john@example.com');

        // Assert Donation Created
        $this->assertDatabaseHas('donations', [
            'amount' => 10000,
            'filing_year' => 2025,
            'status' => DonationStatus::Paid,
        ]);

        // Assert Transaction Created
        $this->assertDatabaseHas('transactions', [
            'amount' => 10000,
            'payment_intent_id' => 'pi_test_123',
        ]);
    });

    it('reuses existing donor by email', function () {
        $existingDonor = Donor::factory()->create([
            'email' => 'existing@example.com',
            'first_name' => 'OldName',
            'user_id' => null,
        ]);

        // Mock PaymentGateway
        $paymentGatewayMock = Mockery::mock(PaymentGateway::class);

        $paymentResult = new PaymentResult(
            id: 'pi_test_456',
            amount: 10000,
            status: 'succeeded',
            livemode: false,
            originalResponse: new PaymentIntent('pi_test_456')
        );

        $paymentGatewayMock->shouldReceive('charge')
            ->once()
            ->andReturn($paymentResult);

        $this->app->instance(PaymentGateway::class, $paymentGatewayMock);

        Livewire::test(DonationForm::class)
            ->set('form.schoolId', $this->school->id)
            ->set('form.filingStatus', FilingStatus::Single->value)
            ->set('form.filingYear', 2025)
            ->set('form.donors.0.first_name', 'NewName') // Should update name
            ->set('form.donors.0.last_name', 'Doe')
            ->set('form.email', 'existing@example.com')
            ->set('form.email_confirmation', 'existing@example.com')
            ->set('form.phone', '(555) 123-4567')
            ->set('form.address', '123 Main St')
            ->set('form.city', 'Phoenix')
            ->set('form.state', 'AZ')
            ->set('form.zip', '85001')
            ->set('form.totalAmount', 100)
            ->set('form.boolQCO', 'no')
            ->set('form.captchaToken', 'dummy-token')
            ->call('submit', 'pm_card_test');

        // Assert Donor Count is still 1
        expect(Donor::count())->toBe(1);

        // Assert Donor was updated
        $this->assertDatabaseHas('donors', [
            'id' => $existingDonor->id,
            'first_name' => 'NewName',
        ]);

        // Assert User Created for existing donor
        $user = User::where('email', 'existing@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->hasRole('donor'))->toBeTrue();
    });
});
