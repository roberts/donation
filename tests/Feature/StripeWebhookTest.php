<?php

use App\Enums\FilingStatus;
use App\Enums\TransactionStatus;
use App\Http\Controllers\StripeWebhookController;
use App\Models\Donation;
use App\Models\School;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->withoutMiddleware(VerifyWebhookSignature::class);
    $this->withoutExceptionHandling(); // Uncomment to debug
});

describe('Stripe Webhook Controller', function () {
    it('handles payment_intent.succeeded event', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'amount' => 10000,
        ]);

        Transaction::factory()->create([
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_test_123',
            'status' => TransactionStatus::Pending,
        ]);

        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'object' => 'payment_intent',
                    'amount' => 10000,
                    'status' => 'succeeded',
                    'livemode' => false,
                    'metadata' => [
                        'school_id' => $this->school->id,
                        'school_name' => $this->school->name,
                        'donation_id' => $donation->id,
                    ],
                ],
            ],
        ];

        // Note: In production, webhook signature verification would be handled by Cashier
        // For testing, we're directly calling the route
        $response = $this->postJson(route('stripe.webhook'), $payload);

        expect($response->getStatusCode())->toBe(200);

        // Verify transaction was created/updated
        $transaction = Transaction::where('donation_id', $donation->id)
            ->where('payment_intent_id', 'pi_test_123')
            ->first();

        expect($transaction)->not->toBeNull()
            ->and($transaction->status)->toBe(TransactionStatus::Succeeded);
    });

    it('handles payment_intent.payment_failed event', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'amount' => 10000,
        ]);

        Transaction::factory()->create([
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_failed_123',
            'status' => TransactionStatus::Pending,
        ]);

        $payload = [
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_failed_123',
                    'object' => 'payment_intent',
                    'amount' => 10000,
                    'status' => 'requires_payment_method',
                    'livemode' => false,
                    'last_payment_error' => [
                        'message' => 'Your card was declined.',
                    ],
                    'metadata' => [
                        'donation_id' => $donation->id,
                    ],
                ],
            ],
        ];

        $controller = new StripeWebhookController;
        $response = $this->postJson(route('stripe.webhook'), $payload);

        expect($response->getStatusCode())->toBe(200);

        // Verify transaction was created with failed status
        $transaction = Transaction::where('donation_id', $donation->id)
            ->where('payment_intent_id', 'pi_failed_123')
            ->first();

        expect($transaction)->not->toBeNull()
            ->and($transaction->status)->toBe(TransactionStatus::Failed);
    });

    it('handles charge.refunded event', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'amount' => 10000,
        ]);

        // Create original transaction
        Transaction::factory()->create([
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_refund_123',
            'amount' => 10000,
            'status' => TransactionStatus::Succeeded,
        ]);

        $payload = [
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_test_123',
                    'object' => 'charge',
                    'amount' => 10000,
                    'amount_refunded' => 10000,
                    'payment_intent' => 'pi_refund_123',
                    'livemode' => false,
                ],
            ],
        ];

        $response = $this->postJson(route('stripe.webhook'), $payload);

        expect($response->getStatusCode())->toBe(200);

        // Verify refund transaction was created
        $transaction = Transaction::where('donation_id', $donation->id)
            ->where('status', TransactionStatus::Refunded)
            ->first();

        expect($transaction)->not->toBeNull()
            ->and($transaction->amount)->toBe(10000);
    });

    it('creates donation from webhook if not exists', function () {
        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_new_from_webhook_123',
                    'object' => 'payment_intent',
                    'amount' => 25000,
                    'status' => 'succeeded',
                    'livemode' => false,
                    'receipt_email' => 'webhook@example.com',
                    'metadata' => [
                        'school_id' => $this->school->id,
                        'school_name' => $this->school->name,
                        'donor_email' => 'webhook@example.com',
                        'filing_year' => date('Y'),
                        'filing_status' => 'married_jointly',
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('stripe.webhook'), $payload);

        expect($response->getStatusCode())->toBe(200);

        // Verify transaction was created
        $transaction = Transaction::where('payment_intent_id', 'pi_new_from_webhook_123')->first();
        expect($transaction)->not->toBeNull();

        // Verify donation was created from webhook data
        $donation = $transaction->donation;

        expect($donation)->not->toBeNull()
            ->and($donation->school_id)->toBe($this->school->id)
            ->and($donation->amount)->toBe(25000)
            ->and($donation->filing_status)->toBe(FilingStatus::MarriedFilingJointly);
    });

    it('does not create donation without school_id in metadata', function () {
        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_no_metadata_123',
                    'object' => 'payment_intent',
                    'amount' => 5000,
                    'status' => 'succeeded',
                    'livemode' => false,
                    'metadata' => [],
                ],
            ],
        ];

        $response = $this->postJson(route('stripe.webhook'), $payload);

        expect($response->getStatusCode())->toBe(200);

        // Verify no transaction was created
        $transaction = Transaction::where('payment_intent_id', 'pi_no_metadata_123')->first();
        expect($transaction)->toBeNull();
    });

    it('updates existing transaction instead of creating duplicate', function () {
        $donation = Donation::factory()->create([
            'school_id' => $this->school->id,
            'amount' => 10000,
        ]);

        // Create an initial pending transaction
        $transaction = Transaction::factory()->create([
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_update_test_123',
            'status' => TransactionStatus::Pending,
        ]);

        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_update_test_123',
                    'object' => 'payment_intent',
                    'amount' => 10000,
                    'status' => 'succeeded',
                    'livemode' => false,
                    'metadata' => [
                        'donation_id' => $donation->id,
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('stripe.webhook'), $payload);

        expect($response->getStatusCode())->toBe(200);

        // Verify only one transaction exists and it was updated
        $transactionCount = Transaction::where('donation_id', $donation->id)
            ->where('payment_intent_id', 'pi_update_test_123')
            ->count();

        expect($transactionCount)->toBe(1);

        $transaction->refresh();
        expect($transaction->status)->toBe(TransactionStatus::Succeeded);
    });
});

describe('Webhook Route', function () {
    it('webhook route exists', function () {
        $route = app('router')->getRoutes()->getByName('stripe.webhook');

        expect($route)->not->toBeNull()
            ->and($route->uri())->toBe('api/webhooks/stripe');
    });
});
