<?php

use App\Actions\Donation\HandleFailedPayment;
use App\Actions\Donation\HandleRefund;
use App\Actions\Donation\HandleSuccessfulPayment;
use App\Enums\DonationStatus;
use App\Enums\TransactionStatus;
use App\Models\Donation;
use App\Models\School;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\StripeObject;

uses(RefreshDatabase::class);

describe('Payment Actions', function () {
    it('handle successful payment creates transaction for existing donation', function () {
        $donation = Donation::factory()->create([
            'amount' => 1000,
        ]);

        Transaction::factory()->create([
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_test_123',
            'amount' => 1000,
            'status' => TransactionStatus::Pending,
        ]);

        $paymentIntent = new PaymentIntent('pi_test_123');
        $paymentIntent->amount = 1000;
        $paymentIntent->livemode = false;
        $paymentIntent->status = 'succeeded';
        $paymentIntent->metadata = StripeObject::constructFrom([
            'donation_id' => $donation->id,
        ]);

        app(HandleSuccessfulPayment::class)->execute($paymentIntent);

        $this->assertDatabaseHas('transactions', [
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_test_123',
            'amount' => 1000,
            'status' => TransactionStatus::Succeeded,
        ]);
    });

    it('handle successful payment creates donation from webhook if missing', function () {
        $paymentIntent = new PaymentIntent('pi_test_new');
        $paymentIntent->amount = 5000;
        $paymentIntent->livemode = false;
        $paymentIntent->status = 'succeeded';
        $paymentIntent->receipt_email = 'newdonor@example.com';
        $paymentIntent->metadata = StripeObject::constructFrom([
            'school_id' => 1,
            'donor_email' => 'newdonor@example.com',
            'filing_year' => 2024,
            'filing_status' => 'single',
            'school_name' => 'Test School',
        ]);
        // Ensure school exists
        School::factory()->create(['id' => 1]);

        app(HandleSuccessfulPayment::class)->execute($paymentIntent);

        $this->assertDatabaseHas('donations', [
            'amount' => 5000,
            'status' => DonationStatus::Paid,
        ]);

        $this->assertDatabaseHas('transactions', [
            'payment_intent_id' => 'pi_test_new',
            'amount' => 5000,
            'status' => TransactionStatus::Succeeded,
        ]);

        $this->assertDatabaseHas('donors', [
            'email' => 'newdonor@example.com',
        ]);
    });

    it('handle failed payment creates failed transaction', function () {
        $donation = Donation::factory()->create();

        Transaction::factory()->create([
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_fail_123',
            'status' => TransactionStatus::Pending,
        ]);

        $paymentIntent = new PaymentIntent('pi_fail_123');
        $paymentIntent->amount = 1000;
        $paymentIntent->livemode = false;
        $paymentIntent->status = 'requires_payment_method';
        $paymentIntent->last_payment_error = new StripeObject([
            'message' => 'Card declined',
        ]);
        $paymentIntent->metadata = StripeObject::constructFrom([
            'donation_id' => $donation->id,
        ]);

        app(HandleFailedPayment::class)->execute($paymentIntent);

        $this->assertDatabaseHas('transactions', [
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_fail_123',
            'status' => TransactionStatus::Failed,
        ]);
    });

    it('handle refund creates refund transaction', function () {
        $donation = Donation::factory()->create();

        // Create original transaction
        \App\Models\Transaction::factory()->create([
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_refund_123',
            'amount' => 1000,
            'status' => TransactionStatus::Succeeded,
        ]);

        $charge = new Charge('ch_test_123');
        $charge->payment_intent = 'pi_refund_123';
        $charge->amount_refunded = 500;
        $charge->livemode = false;

        app(HandleRefund::class)->execute($charge);

        $this->assertDatabaseHas('transactions', [
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_refund_123',
            'amount' => 500,
            'status' => TransactionStatus::Refunded,
        ]);

        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'status' => DonationStatus::Refunded,
        ]);
    });
});
