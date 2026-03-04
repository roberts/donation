# Application Logic & Migration Analysis

## Overview
This document outlines the application logic derived from the legacy Symfony/ArrestDB/PHP codebase and the target architecture for the Laravel 12 migration.

## Tech Stack
- **Framework**: Laravel 12
- **Frontend**: TALL Stack (Tailwind, Alpine, Laravel, Livewire)
- **Styling**: Tailwind CSS v4
- **Testing**: Pest v4.1
- **Database**: MySQL

## Database Schema

### Users
Migrated from Symfony `User` entity.
- `id`: Primary Key
- `username`: String, Unique
- `email`: String
- `password`: String (Hashed)
- `roles`: JSON (e.g., `["ROLE_ADMIN"]`)
- `full_name`: String

### Schools
Migrated from `school` table.
- `id`: Primary Key
- `ibe_id`: Integer (Legacy ID)
- `type`: String (e.g., "private", "public")
- `name`: Text

### Donations
Core entity for tracking contributions.
- `id`: Primary Key
- `payment_intent_id`: String (Stripe)
- `amount`: Integer (in cents)
- `email`: String
- `title`, `first_name`, `last_name`: Donor details
- `title2`, `first_name2`, `last_name2`: Secondary donor details (nullable)
- `filing_status`: String
- `filing_year`: Integer
- `address_street1`, `address_city`, `address_state`, `address_postal_code`, `address_country`: Mailing address
- `billing_address_*`: Billing address fields
- `phone_number`: String
- `qco`: String (Qualified Charitable Organization code)
- `school_donation_id`: Integer (Reference to School)
- `school_donation_name`: String (Snapshot of school name)
- `tax_professional_name`, `tax_professional_phone`, `tax_professional_email`: Tax pro details
- `created_at`: DateTime

### Transactions
Stripe transaction log.
- `id`: Primary Key
- `payment_intent_id`: String
- `amount`: Integer
- `status`: String
- `livemode`: Boolean
- `created_at`: DateTime

## Core Features

### 1. Public Donation Portal
- **Route**: `/`
- **Logic**:
    - Accepts school ID via query param (`?schoolId=...`) to pre-select/display school.
    - Collects donor info, tax filing status, and amount.
    - Integrates with Stripe Elements for payment processing.
    - **Legacy**: Was `index.php` + `stripesecret/index.php`.
    - **New**: Inertia Page `Donation/Create.vue` + `DonationController@store`.

### 2. Admin Panel
- **Route**: `/admin`
- **Logic**:
    - Dashboard for overview.
    - **Donations**: List view with filters (Year, Name). Detail view.
        - Actions: View Receipt (PDF), Email Receipt.
    - **Schools**: CRUD operations.
    - **Transactions**: Read-only view of Stripe events.
    - **Legacy**: EasyAdmin (Symfony).
    - **New**: Filament v4.3 (Admin Panel).
        - **Resources**: `DonationResource`, `SchoolResource`, `TransactionResource`.
        - **Widgets**: Dashboard stats (Total Donations, Recent Activity).
        - **Actions**: Custom Actions on Donation Resource for "View Receipt" and "Email Receipt".

### 3. API
- **Route**: `/api/*`
- **Logic**:
    - **Legacy**: ArrestDB (`api/index.php`).
    - **New**: **REMOVED**. The legacy API was only required due to codebase fragmentation. The new Inertia/Vue architecture allows direct data passing, eliminating the need for a separate external API.

### 4. Webhooks
- **Route**: `/webhooks/stripe`
- **Logic**:
    - Listens for `payment_intent.succeeded`.
    - Creates/Updates `Transaction` record.
    - Creates `Donation` record if successful payment.
    - **Legacy**: `webhooks/stripe/index.php`.
    - **New**: Laravel Cashier (Webhook handling) or custom `StripeWebhookController`.

### 5. Receipt Generation
- **Logic**:
    - Generates PDF receipt for tax purposes.
    - Template includes Donor Name, Address, Amount, Date, Filing Year.
    - **Legacy**: `mpdf` with HTML templates.
    - **New**: `spatie/laravel-pdf` with Blade templates (Tailwind CSS support).

### 6. Email Notifications
- **Logic**:
    - Sends receipt PDF to donor via email.
    - Uses Resend for transactional emails.
    - **Legacy**: SwiftMailer + AWS SES.
    - **New**: Laravel Mailables + Resend Driver.

## Migration Strategy
1.  **Database**: Run migrations to create new schema.
2.  **Data**: Import existing data from legacy tables if needed (or start fresh if fresh install).
3.  **Code**: Implement Models, Controllers, and Vue Pages.
4.  **Cleanup**: Remove `admin/`, `api/`, `stripesecret/`, `webhooks/`, `assets/` directories.
