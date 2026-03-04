# Feature Specification: Migrate Legacy App Logic

**Feature Branch**: `001-migrate-legacy-app`
**Created**: 2025-12-10
**Status**: Draft
**Input**: User description: "Full detailed implementation of migrating all previous app logic to Laravel 12 so those previous directories & all code files can be deleted without losing any functionality"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Public Donation Flow (Priority: P1)

As a donor, I want to be able to visit the website, select a school, and make a donation using my credit card so that I can support the foundation.

**Why this priority**: This is the core revenue generation feature of the application.

**Independent Test**: Can be tested by visiting the home page, filling out the form, and completing a payment with a test card.

**Acceptance Scenarios**:

1. **Given** I am on the home page, **When** I fill out the donation form and submit valid payment details, **Then** a donation record is created, a transaction is recorded, and I see a success message.
2. **Given** I visit the site with `?schoolId=123`, **When** the page loads, **Then** the school with ID 123 is pre-selected or displayed.
3. **Given** I submit invalid payment details, **When** the payment fails, **Then** I see an error message and no donation is recorded.

---

### User Story 2 - Stripe Webhook Processing (Priority: P1)

As the system, I want to listen for Stripe payment events so that I can record transactions and donations reliably, even if the user closes their browser.

**Why this priority**: Ensures data integrity for financial records.

**Independent Test**: Can be tested by triggering a mock Stripe webhook event to the endpoint.

**Acceptance Scenarios**:

1. **Given** a `payment_intent.succeeded` webhook event, **When** it is received, **Then** the corresponding Transaction record is updated to 'succeeded'.
2. **Given** a `payment_intent.succeeded` event for a new donation, **When** it is received, **Then** a new Donation record is created if it doesn't exist.

---

### User Story 3 - Admin Dashboard & Management (Priority: P2)

As an administrator, I want to log in to a dashboard where I can manage schools and view donations so that I can oversee the foundation's operations.

**Why this priority**: Critical for operational management and data oversight.

**Independent Test**: Can be tested by logging in as an admin and performing CRUD operations.

**Acceptance Scenarios**:

1. **Given** I am logged in as an admin, **When** I visit the dashboard, **Then** I see an overview of donations.
2. **Given** I am on the Schools page, **When** I add a new school, **Then** it appears in the list and is available on the public donation form.
3. **Given** I am on the Donations page, **When** I filter by year, **Then** only donations from that year are displayed.

---

### User Story 4 - Receipt Generation & Emailing (Priority: P2)

As an admin, I want to be able to view and email PDF receipts to donors so that they have proof of their tax-deductible contribution.

**Why this priority**: Legal requirement for tax-deductible donations.

**Independent Test**: Can be tested by clicking "View Receipt" or "Email Receipt" in the admin panel.

**Acceptance Scenarios**:

1. **Given** a donation record, **When** I click "View Receipt", **Then** a PDF is generated and opened in a new tab with correct donor and amount details.
2. **Given** a donation record, **When** I click "Email Receipt", **Then** the donor receives an email with the PDF receipt attached.

### Edge Cases

- **Payment Failure**: If Stripe declines the card, the user should see a clear error message and no donation record should be finalized.
- **Webhook Idempotency**: If Stripe sends the same webhook event twice, the system must not create duplicate transactions or donations.
- **Email Failure**: If the receipt email fails to send (e.g., Resend error), the system should log the error but not fail the entire transaction process.
- **Rate Limiting**: If a user or bot exceeds the allowed number of donation attempts, the system must block the request and return a 429 Too Many Requests response.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST migrate the database schema for Users, Schools, Donations, and Transactions to Laravel migrations.
- **FR-002**: System MUST provide a public-facing donation form using the TALL stack (Tailwind, Alpine, Laravel, Livewire).
- **FR-003**: System MUST handle Stripe `payment_intent.succeeded` webhooks to create/update Transaction and Donation records.
- **FR-004**: System MUST provide an Admin Panel using **Filament v4.3** protected by authentication.
- **FR-005**: Filament Admin Panel MUST allow CRUD operations for Schools.
- **FR-006**: Filament Admin Panel MUST allow viewing and filtering (by year, name) of Donations.
- **FR-007**: System MUST generate PDF receipts matching the legacy format (using `spatie/laravel-pdf` for Tailwind CSS compatibility).
- **FR-008**: System MUST be able to email PDF receipts to donors via **Resend**.
- **FR-010**: System MUST allow deletion of legacy directories (`admin`, `api`, `stripesecret`, `webhooks`, `assets`) upon completion.
- **FR-011**: System MUST use `spatie/laravel-permission` for managing admin access and roles.
- **FR-012**: System MUST use `spatie/laravel-data` for handling complex donation form data and validation.
- **FR-013**: System MUST use `laravel/cashier` for Stripe integration, ensuring a User record is created/retrieved for every donor to leverage the `Billable` trait.
- **FR-014**: System MUST implement rate limiting on the donation submission endpoint to prevent card testing attacks.
- **FR-015**: System MUST process email notifications and PDF generation asynchronously using Laravel Queues to ensure immediate UI feedback.
- **FR-016**: System MUST include **Laravel Pulse** for performance monitoring, restricted to authenticated admin users.

### Key Entities *(include if feature involves data)*

- **User**: Admin users with access to the dashboard.
- **School**: Beneficiary institutions for donations.
- **Donation**: Record of a successful contribution.
- **Transaction**: Log of Stripe payment intents and their status.

## Success Criteria

1.  **Legacy Cleanup**: The `admin`, `api`, `stripesecret`, `webhooks`, and `assets` directories are deleted, and the application runs entirely on Laravel 12.
2.  **Donation Success**: Users can successfully complete a donation flow, including payment processing and receipt generation.
3.  **Admin Parity**: Admins can perform all management tasks (Schools CRUD, Donation viewing, Receipt emailing) available in the legacy system.
4.  **Test Coverage**: Critical paths (Donation flow, Webhook handling) are covered by automated tests.

## Assumptions

- The existing `.env` configuration for Stripe is valid. New configuration for Resend will be required.
- The legacy database data (if any) will be migrated or is not required for the fresh install (user mentioned "fresh Laravel app", implying migration of logic/schema is key, data import might be separate or fresh start). *Assumption: We are building the structure to hold the data.*
- "Don't write code" in the prompt applied to the analysis phase; this spec is for the implementation phase where code *will* be written.
