# Livewire Migration Plan: Donation Form

This document outlines the step-by-step plan to migrate the legacy Vue/FormKit donation form to a server-side rendered Laravel 12 + Livewire 3.7 application. The goal is a pixel-perfect recreation with identical functionality.

## Phase 1: Foundation & Setup

- [x] **Layout Configuration**
    - Create a dedicated layout `resources/views/components/layouts/donation.blade.php`.
    - Implement the "IBE Blue" (`#183762`) header with the logo and navigation links.
    - Implement the footer with contact info and EIN.
    - Ensure `Montserrat` and `Roboto` fonts are loaded via Google Fonts.
    - Configure the main container `max-w-[1080px]` with white background and shadow.

- [x] **Tailwind Configuration**
    - Verify `tailwind.config.js` (or CSS theme) includes custom colors:
        - `ibe-blue`: `#183762`
        - `form-accent`: `#286090`
        - `form-valid`: `#3c3` / `#84cc16`
    - Ensure form input styles match FormKit defaults (border-slate-400, focus rings).

- [x] **Database & Models**
    - Ensure `School` model exists and has `name` and `type` attributes.
    - Ensure `Donation` model exists with all required fields from the legacy payload.
    - Create `Transaction` model if not exists for Stripe payment logging.

## Phase 2: Component Architecture

- [x] **Create Livewire Component**
    - Run `php artisan make:livewire DonationForm`.
    - Define public properties for current step tracking: `public $currentStep = 1;`.
    - Implement `mount()` to handle `?schoolId=` query parameter logic (fetch school, set initial state).

- [x] **Create Form Object**
    - Create `App\Livewire\Forms\DonationForm.php`.
    - Move all form fields into this class to keep the main component clean.
    - Define properties:
        - **Step 1:** `filingStatus`, `donors` (array), `phone`, `address`, `city`, `state`, `zip`, `email`, `email_confirmation`.
        - **Step 2:** `filingYear`, `boolQCO`, `qcoName`, `qcoAmount`.
        - **Step 3:** `totalAmount`, `schoolId`.
        - **Step 4:** `billingAddressEnable`, `billingAddress` fields, `paymentMethodId`.
        - **Step 5:** `taxProfessionalEnable`, `taxProfessional` fields.

## Phase 3: Step 1 - Your Information

- [x] **UI Implementation**
    - Create the step header with dynamic styling:
        - Blue (`#286090`) when invalid/active.
        - Green (`#3c3`) with checkmark icon when valid/completed.
    - Implement `Filing Status` dropdown.
    - Implement `Donors` repeater logic:
        - Allow adding 2nd donor only if "Married Filing Jointly" is selected.
        - Hide "Add Secondary Donor" button when max reached.
    - Implement `Phone` input with masking (use Alpine.js `@mask` or similar).
    - Implement Address/Email grid layout.

- [x] **Validation**
    - Add `#[Validate]` rules to the Form object.
    - Implement `updated($propertyName)` hooks for real-time validation feedback.
    - Ensure "Confirm Email" matches "Email".

## Phase 4: Step 2 - Tax Year & QCO

- [x] **UI Implementation**
    - Implement `Filing Year` radio buttons (dynamic based on server config).
    - Implement `boolQCO` radio buttons (Yes/No).
    - Create conditional section for QCO details (Name/Amount) using `@if`.

- [x] **Logic**
    - Reset `totalAmount` if `filingStatus` or `filingYear` changes.
    - Display the "QCO Confirmation Message" dynamically when QCO amount is entered.

## Phase 5: Step 3 - Donation & School

- [x] **Tax Credit Calculation**
    - Implement a backend service or helper `TaxCreditCalculator`.
    - Logic: `MaxCredit - QCOAmount = MaxDonation`.
    - Limits:
        - 2024: $470 (Single) / $938 (Married)
        - 2025: $495 (Single) / $987 (Married)
        - 2026: $521 (Single) / $1,039 (Married)

- [x] **UI Implementation**
    - Implement `Donation Amount` input with "Calculator" icon button.
    - **Calculator Action:** Clicking the icon calls `$this->calculateMaxDonation()` and fills the input.
    - Implement `School Selection` autocomplete:
        - Use a searchable dropdown (Combobox pattern).
        - Search `School` model via `like` query.
        - Show "Green background" confirmation box when school is selected.

## Phase 6: Step 4 - Payment (Stripe)

- [x] **Stripe Integration**
    - Include Stripe.js v3 in the layout head.
    - Create a dedicated Blade component for the Stripe Element (e.g., `<x-stripe-element />`).
    - Use `wire:ignore` on the element container to prevent Livewire DOM diffing issues.
    - Use Alpine.js to initialize Stripe Elements and handle the `change` event to update validation state.

- [x] **Billing Address Logic**
    - Implement "Same as above" checkbox.
    - Conditionally show billing address fields if unchecked.

## Phase 7: Step 5 - Tax Preparer & Submission

- [x] **Tax Preparer Section**
    - Implement "Permission given" checkbox.
    - Conditionally show contact fields.

- [x] **Submission Logic**
    - **Frontend:**
        - On submit, use Stripe.js to `createPaymentMethod`.
        - If successful, pass `paymentMethodId` to Livewire via `$wire.submit(paymentMethodId)`.
        - Handle errors (display in red text).
    - **Backend (`submit` method):**
        - Validate all steps one last time.
        - Create `Donation` record.
        - Process payment via Laravel Cashier or Stripe SDK.
        - Send `DonationReceipt` email.
        - Redirect to success route.

## Phase 8: Success Page

- [x] **Success View**
    - Create `resources/views/donation/success.blade.php`.
    - Display "Thank you" message.
    - Link to `ibescholarships.org`.

## Phase 9: Testing & QA

- [ ] **Unit/Feature Tests**
    - Test tax credit calculations for all years/statuses.
    - Test validation rules for each step.
    - Test school lookup search.
    - Test submission flow (mocking Stripe).

- [ ] **Browser Tests (Dusk/Pest)**
    - Verify multi-step navigation.
    - Verify dynamic UI elements (repeater, conditional fields).
    - Verify Stripe element loading.

- [ ] **Visual QA**
    - Compare fonts, colors, and spacing against legacy screenshots/app.
    - Verify mobile responsiveness (grid collapsing).
