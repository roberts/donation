# Research Findings: Migrate Legacy App Logic

**Feature**: `001-migrate-legacy-app`
**Date**: 2025-12-10

## 1. Filament v4.3 Actions & Dashboard

### Actions Implementation
To implement the requested actions on the `DonationResource`, we will use Filament's Action API.

**A. View Receipt (Open PDF in New Tab)**
Use the `url()` method with `openUrlInNewTab()`. This assumes a route `receipt.show` exists that generates the PDF.

```php
use Filament\Tables\Actions\Action;
use App\Models\Donation;

Action::make('view_receipt')
    ->label('View Receipt')
    ->icon('heroicon-o-document-text')
    ->url(fn (Donation $record) => route('receipt.show', $record))
    ->openUrlInNewTab();
```

**B. Email Receipt (Queued Mailable)**
Use the `action()` method to trigger the logic. We will use Laravel's `Mail` facade to queue the email and Filament's `Notification` facade for feedback.

```php
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\DonationReceipt;

Action::make('email_receipt')
    ->label('Email Receipt')
    ->icon('heroicon-o-envelope')
    ->requiresConfirmation()
    ->action(function (Donation $record) {
        Mail::to($record->email)->queue(new DonationReceipt($record));
        
        Notification::make()
            ->title('Receipt Sent')
            ->success()
            ->send();
    });
```

### Dashboard Stats
In Filament, dashboard stats are created using **Stats Overview Widgets**.

**Implementation:**
1.  Run `php artisan make:filament-widget DashboardStatsOverview --stats-overview`.
2.  Define the stats in the `getStats()` method.

```php
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Donation;

class DashboardStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Donations', Donation::count())
                ->description('All time donations')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Recent Activity', Donation::where('created_at', '>=', now()->subDays(7))->count())
                ->description('Last 7 days')
                ->color('primary'),
        ];
    }
}
```

## 2. Resend Integration

### Configuration in Laravel 12
Laravel 12 supports Resend via the first-party driver or the `resend/resend-php` community package.

**Steps:**
1.  **Install SDK:** `composer require resend/resend-php`
2.  **Configure `.env`:**
    ```ini
    MAIL_MAILER=resend
    RESEND_KEY=re_123456789
    ```
3.  **Configure `config/services.php`:**
    ```php
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    ```

### Webhook Handling
Resend webhooks (e.g., for "Email Delivery Failed") should be handled via a dedicated controller.

**Strategy:**
1.  Create `ResendWebhookController`.
2.  Define a route: `Route::post('/webhooks/resend', [ResendWebhookController::class, 'handle']);`.
3.  Verify the signature and update the `Donation` or `Transaction` status based on the event (e.g., `email.bounced`).

## 3. Legacy Schema Migration

### Schema Analysis & Mapping

The legacy schema is flat and uses some non-standard naming conventions. We will map these to standard Laravel Eloquent models.

| Legacy Table | New Model | Notes |
| :--- | :--- | :--- |
| `school` | `School` | Direct mapping. |
| `Donations` | `Donation` | Rename table to `donations` (lowercase, plural). |
| `Transactions` | `Transaction` | Rename table to `transactions`. |

### Column Renaming & Optimization

**`donations` Table:**
*   `school_donation_id` -> `school_id` (Foreign Key).
*   `school_donation_name` -> `school_name_snapshot` (To clarify it's a historical snapshot, not a relation).
*   `payment_intent_id` -> `stripe_payment_intent_id` (Clearer vendor prefix).
*   `amount` -> `amount_cents` (Explicit unit naming).
*   **Address Fields:** Keep flat fields `billing_address_*` / `mailing_address_*` for simplicity in SQL reporting, but cast them to a Data Object in the model if needed.

### Cashier & Billable Trait Strategy

**Decision:**
*   **Guest Checkout:** For one-off donations where the user does not create an account, we will **not** use the `Billable` trait on a `User` model for every donor.
*   **Implementation:**
    1.  **Use Stripe Elements + Cashier (or Stripe SDK) for the charge:** We can use `Cashier::stripe()->paymentIntents->create(...)`.
    2.  **Store Data in `donations`:** We will store the `stripe_payment_intent_id` in our `donations` table.
    3.  **Transactions Table:** Keep the `transactions` table as a local log of Stripe Webhook events (`payment_intent.succeeded`), linked to the `Donation`.
