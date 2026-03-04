<?php

use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Stripe webhooks - Uses our custom controller that extends Cashier's WebhookController
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');
