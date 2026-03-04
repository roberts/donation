<?php

namespace App\Http\Controllers;

use App\Actions\Donation\HandleFailedPayment;
use App\Actions\Donation\HandleRefund;
use App\Actions\Donation\HandleSuccessfulPayment;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Stripe\Charge;
use Stripe\Event;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle the payment_intent.succeeded webhook event.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handlePaymentIntentSucceeded(array $payload): Response
    {
        $event = Event::constructFrom($payload);
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;

        app(HandleSuccessfulPayment::class)->execute($paymentIntent);

        return $this->successMethod();
    }

    /**
     * Handle the payment_intent.payment_failed webhook event.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handlePaymentIntentPaymentFailed(array $payload): Response
    {
        $event = Event::constructFrom($payload);
        /** @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;

        app(HandleFailedPayment::class)->execute($paymentIntent);

        return $this->successMethod();
    }

    /**
     * Handle the charge.refunded webhook event.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleChargeRefunded(array $payload): Response
    {
        $event = Event::constructFrom($payload);
        /** @var Charge $charge */
        $charge = $event->data->object;

        app(HandleRefund::class)->execute($charge);

        return $this->successMethod();
    }
}
