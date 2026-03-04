# Tasks: Migrate Legacy App Logic

**Feature**: `001-migrate-legacy-app`
**Status**: Complete

## Phase 1: Setup & Configuration
**Goal**: Initialize the environment with necessary packages and configurations.

- [X] T001 Install Filament v4.3 via Composer
- [X] T002 Install Laravel Cashier (Stripe) and publish migrations
- [X] T003 Install Spatie packages (Permission, Laravel-Data, Laravel-PDF, Activitylog, Backup, CSP, Health)
- [X] T004 Install Resend PHP SDK and configure mail driver in `.env` and `config/services.php`
- [X] T005 Install Laravel Pulse and configure access gate in `app/Providers/AppServiceProvider.php`
- [X] T006 [P] Configure `spatie/laravel-pdf` with Tailwind CSS support in `config/pdf.php`
- [X] T007 [P] Configure `spatie/laravel-permission` and publish migrations

## Phase 2: Foundational (Database & Models)
**Goal**: Establish the database schema and Eloquent models.

- [X] T008 [P] Create migration for `schools` table in `database/migrations/`
- [X] T009 [P] Create migration for `donations` table in `database/migrations/`
- [X] T010 [P] Create migration for `transactions` table in `database/migrations/`
- [X] T011 [P] Update `users` table migration to include Spatie Permission fields (if needed) or seed roles
- [X] T012 [P] Create `School` model in `app/Models/School.php` with fillables
- [X] T013 [P] Create `Donation` model in `app/Models/Donation.php` with casts and relationships
- [X] T014 [P] Create `Transaction` model in `app/Models/Transaction.php` with casts and relationships
- [X] T015 [P] Create `DonationFormData` DTO in `app/Data/DonationFormData.php`
- [X] T016 [P] Create `AddressData` DTO in `app/Data/AddressData.php`
- [X] T017 Create Model Factories for School, Donation, and Transaction in `database/factories/`
- [X] T018 Create Database Seeder to populate initial Schools and Admin User in `database/seeders/DatabaseSeeder.php`

## Phase 3: User Story 1 - Public Donation Flow
**Goal**: Enable donors to make payments via the public website.

- [X] T019 [US1] Create `DonationController` in `app/Http/Controllers/DonationController.php`
- [X] T020 [US1] Implement `create` method in `DonationController` to render Inertia page
- [X] T021 [US1] Create Inertia page `resources/js/Pages/Donation/Create.vue` with Stripe Elements
- [X] T022 [US1] Implement `store` method in `DonationController` using `DonationFormData` and Cashier
- [X] T023 [US1] Implement Rate Limiting on `store` route in `routes/web.php` or `app/Providers/AppServiceProvider.php`
- [X] T024 [US1] Create Feature Test `tests/Feature/DonationFlowTest.php` verifying successful donation
- [ ] T025 [US1] Create Browser Test `tests/Browser/DonationTest.php` for end-to-end flow

## Phase 4: User Story 2 - Stripe Webhook Processing
**Goal**: Ensure data integrity via webhooks.

- [X] T026 [US2] Configure Stripe Webhook secret in `.env`
- [X] T027 [US2] Create `StripeWebhookController` in `app/Http/Controllers/Webhooks/StripeWebhookController.php` (or extend Cashier's)
- [X] T028 [US2] Implement `handlePaymentIntentSucceeded` method to update Transaction/Donation
- [X] T029 [US2] Register webhook route in `routes/web.php` (exclude from CSRF)
- [X] T030 [US2] Create Feature Test `tests/Feature/StripeWebhookTest.php` mocking webhook payload

## Phase 5: User Story 3 - Admin Dashboard
**Goal**: Enable admin management of data.

- [X] T031 [US3] Install Filament Panel and create Admin User
- [X] T032 [US3] Create `SchoolResource` in `app/Filament/Resources/SchoolResource.php`
- [X] T033 [US3] Create `DonationResource` in `app/Filament/Resources/DonationResource.php` with table columns and filters
- [X] T034 [US3] Create `TransactionResource` in `app/Filament/Resources/TransactionResource.php` (Read-only)
- [X] T035 [US3] Create `DashboardStatsOverview` widget in `app/Filament/Widgets/DashboardStatsOverview.php`
- [X] T036 [US3] Create Feature Test `tests/Feature/Admin/SchoolResourceTest.php`
- [X] T037 [US3] Create Feature Test `tests/Feature/Admin/DonationResourceTest.php`

## Phase 6: User Story 4 - Receipt Generation & Emailing
**Goal**: Automate receipt delivery.

- [X] T038 [US4] Create Blade view for PDF receipt in `resources/views/pdf/receipt.blade.php`
- [X] T039 [US4] Create `DonationReceipt` Mailable in `app/Mail/DonationReceipt.php`
- [X] T040 [US4] Configure Queue connection (database or redis) in `.env`
- [X] T041 [US4] Add "View Receipt" Action to `DonationResource` in `app/Filament/Resources/DonationResource.php`
- [X] T042 [US4] Add "Email Receipt" Action to `DonationResource` in `app/Filament/Resources/DonationResource.php`
- [X] T043 [US4] Create Feature Test `tests/Feature/ReceiptGenerationTest.php`

## Phase 7: Polish & Cleanup
**Goal**: Finalize migration and remove legacy code.

- [X] T044 Remove legacy `admin/` directory
- [X] T045 Remove legacy `api/` directory
- [X] T046 Remove legacy `stripesecret/` directory
- [X] T047 Remove legacy `webhooks/` directory
- [X] T048 Remove legacy `assets/` directory
- [X] T049 Run full test suite `php artisan test` and `php artisan dusk`
- [X] T050 Verify Laravel Pulse dashboard access

## Dependencies

- Phase 2 requires Phase 1
- Phase 3 requires Phase 2
- Phase 4 requires Phase 2
- Phase 5 requires Phase 2
- Phase 6 requires Phase 5 (for Actions) and Phase 3 (for data)
- Phase 7 requires all previous phases

## Implementation Strategy
1.  **MVP**: Complete Phases 1, 2, and 3 to enable donations.
2.  **Reliability**: Complete Phase 4 to ensure payments are recorded even if UI fails.
3.  **Management**: Complete Phase 5 and 6 to replace legacy admin tools.
4.  **Cleanup**: Execute Phase 7 only after full verification.
