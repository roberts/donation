# Data Model: Migrate Legacy App Logic

**Feature**: `001-migrate-legacy-app`

## Entity Relationship Diagram

```mermaid
erDiagram
    User ||--o{ Donation : "manages (optional)"
    School ||--o{ Donation : "receives"
    Donation ||--o{ Transaction : "has"

    User {
        bigint id PK
        string name
        string email
        string password
        json roles "Spatie Permission"
    }

    School {
        bigint id PK
        integer legacy_id "ibe_id"
        string name
        string type "public, private"
        timestamp created_at
        timestamp updated_at
    }

    Donation {
        bigint id PK
        bigint school_id FK
        string stripe_payment_intent_id
        integer amount_cents
        string donor_email
        string donor_name
        string donor_phone
        json billing_address
        json mailing_address
        integer filing_year
        string filing_status
        string school_name_snapshot
        timestamp receipt_sent_at
        timestamp created_at
        timestamp updated_at
    }

    Transaction {
        bigint id PK
        bigint donation_id FK
        string stripe_payment_intent_id
        string status "succeeded, failed"
        integer amount_cents
        json payload "Stripe Webhook Payload"
        timestamp created_at
        timestamp updated_at
    }
```

## Models & Schemas

### 1. User (Admin)
Standard Laravel User model with Spatie Permission traits.
- **Table**: `users`
- **Traits**: `HasRoles`

### 2. School
Beneficiary institution.
- **Table**: `schools`
- **Fillable**: `name`, `type`, `legacy_id`
- **Relationships**:
    - `donations()`: HasMany `Donation`

### 3. Donation
Core record of a contribution.
- **Table**: `donations`
- **Casts**:
    - `amount_cents`: `integer`
    - `billing_address`: `array` (or `Spatie\LaravelData\Data`)
    - `mailing_address`: `array` (or `Spatie\LaravelData\Data`)
    - `receipt_sent_at`: `datetime`
- **Relationships**:
    - `school()`: BelongsTo `School`
    - `transactions()`: HasMany `Transaction`

### 4. Transaction
Audit log of Stripe events.
- **Table**: `transactions`
- **Casts**:
    - `payload`: `array`
- **Relationships**:
    - `donation()`: BelongsTo `Donation`

## Data Transfer Objects (DTOs)

### DonationFormData
Used for validating and transferring data from the Inertia form to the Controller.

```php
class DonationFormData extends Data
{
    public function __construct(
        public int $school_id,
        public int $amount_cents,
        public string $donor_first_name,
        public string $donor_last_name,
        public string $donor_email,
        public string $donor_phone,
        public AddressData $billing_address,
        public ?AddressData $mailing_address,
        public int $filing_year,
        public string $filing_status,
        public string $payment_method_id, // From Stripe Elements
    ) {}
}
```

### AddressData
Reusable address structure.

```php
class AddressData extends Data
{
    public function __construct(
        public string $street,
        public string $city,
        public string $state,
        public string $zip,
        public string $country = 'US',
    ) {}
}
```
