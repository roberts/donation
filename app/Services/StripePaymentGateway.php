<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Data\PaymentData;
use App\Data\PaymentResult;
use App\Exceptions\PaymentFailedException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripePaymentGateway implements PaymentGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function charge(PaymentData $data): PaymentResult
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $data->amount,
                'currency' => $data->currency,
                'payment_method' => $data->token,
                'confirm' => true,
                'description' => $data->description,
                'metadata' => $data->metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

            return new PaymentResult(
                id: $paymentIntent->id,
                amount: $paymentIntent->amount,
                status: $paymentIntent->status,
                livemode: $paymentIntent->livemode,
                originalResponse: $paymentIntent
            );

        } catch (CardException $e) {
            throw PaymentFailedException::declined($e->getMessage());
        } catch (ApiErrorException $e) {
            throw PaymentFailedException::providerError($e->getMessage());
        }
    }
}
