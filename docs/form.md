# IBE Foundation Donation Form - Complete Documentation

## Overview

The legacy donation form is a multi-step FormKit-powered form that collects donor information, tax year details, donation amounts, school preferences, and payment information for Arizona QCO tax credit donations.

**URL:** `https://app.ibefoundation.org/` (legacy) / `/donate` (new app)

---

## Global Styling

### Fonts
- **Primary Font:** `Montserrat` - Used for body text
- **Form Font:** `Roboto` (Helvetica, Arial, Lucida, sans-serif fallback) - Used in form fields, labels, and the `.roboto` class

### Colors
| Color Name | Hex Code | Usage |
|------------|----------|-------|
| IBE Blue | `#183762` | Header/footer background, submit button |
| Form Accent (Legacy) | `#286090` | Step headers (invalid state) |
| Form Valid | `#3c3` / `#84cc16` (lime-500) | Step headers (valid state), checkboxes |
| White | `#ffffff` | Form card background, text on dark backgrounds |
| Slate 700 | `#334155` | Label text color |
| Slate 400 | `#94a3b8` | Input borders, placeholder text |
| Red 600 | `#dc2626` | Error messages, required asterisks |
| Gray 100 | `#f3f4f6` | Body background |

### Layout
- Form container: `max-w-[1080px]`, centered, white background
- Card styling: `rounded-md shadow-md p-8`
- Grid gaps: `gap-4`
- Section spacing: `mb-8` between fieldsets

---

## Page Structure

### Header
- Navy blue background (`#183762`)
- IBE Foundation logo (centered, links to `https://ibefoundation.org/`)
- Navigation menu (About Us, What We Do, Donate Now)
- Logo dimensions: `max-height: 50px`, scaled 1.1x

### Footer
- Navy blue background (`#183762`)
- Contact info: `921 North Swan Road, Tucson, Arizona 85711 | 520-200-7275 | services@ibefoundation.org`
- EIN: `93-1967631`
- White IBE Foundation logo (`max-height: 80px`)

---

## Form Sections

### Page Title
```html
<h1>IBE Foundation Donation</h1>
<h2></h2> <!-- Empty subtitle -->
```
- Centered, Roboto font
- `margin-top: 1.5rem`

---

## Step 1: Your Information (1 of 4)

### Step Header
- Background: `#286090` (invalid) → `#3c3`/lime-500 (valid)
- Text: White, 2xl size
- Right side: Checkmark icon (`circle-check`) when valid
- Transition: `background-color 0.5s ease`

### Fields

#### Filing Status (Dropdown)
| Property | Value |
|----------|-------|
| Type | `dropdown` |
| Name | `filingStatus` |
| Label | "Filing Status" |
| Validation | `required` |
| Options | Dynamic from server (`filing_statuses` prop) |

**Values:**
- Single (value: 3)
- Married Filing Jointly (value: 1)
- Head of Household (value: 2)
- Married Filing Separately (value: 4)

#### Donors (Repeater)
| Property | Value |
|----------|-------|
| Type | `repeater` |
| Name | `donors` |
| Label | "Donors" |
| Add Button Label | "+ Secondary Donor" |
| Min | 1 |
| Max | 2 (if Married Filing Jointly, value=1), else 1 |
| Draggable | false |

**Repeater Item Fields (5-column grid on md+):**

| Field | Type | Grid Span | Validation |
|-------|------|-----------|------------|
| Title | `dropdown` | 1 col | Optional |
| First Name | `text` | 2 cols | `required` |
| Last Name | `text` | 2 cols | `required` |

**Title Options:**
- Dr., Mr., Mrs., Ms.

**Add Button Behavior:**
- Hidden when max reached (`[data-disabled="true"] { display: none }`)

#### Phone (Mask Input)
| Property | Value |
|----------|-------|
| Type | `mask` |
| Name | `phone` |
| Label | "Primary Phone" |
| Mask | `(###) ###-####` |
| Suffix Icon | `phone` (FontAwesome) |
| Validation | Custom `telValidate` - must be 10 digits or empty |
| Validation Visibility | `live` |

#### Address Section (4-column grid on md+)
| Field | Type | Validation | Notes |
|-------|------|------------|-------|
| Address | `text` | `required` | |
| City | `text` | `required` | |
| State | `dropdown` | `required` | All 50 US states + DC |
| Zip Code | `mask` | `required` | Mask: `#####` |

#### Email Section (2-column grid on md+)
| Field | Type | Validation | Notes |
|-------|------|------------|-------|
| Email Address | `email` | `required` | Suffix icon: `envelope` |
| Confirm Email | `email` | `required`, `confirm` | Suffix icon: `envelope` |

#### Information Note
> "The opportunity to recommend a school will become available as soon as the amount of your donation is added below."

---

## Step 2: Tax Year and QCO Donations (2 of 4)

### Fields

#### Filing Year (Radio)
| Property | Value |
|----------|-------|
| Type | `radio` |
| Name | `filingYear` |
| Label | "What tax year will you be claiming this Credit on your taxes?" |
| Help | "The tax year that you will claim your QCO donation. You have until April 15 each year to donate for the prior year." |
| Options | Dynamic from server (`filing_years` prop) - typically 2024, 2025, 2026 |
| Validation | `required` |
| Disabled | Until filing status selected in Step 1 |

#### QCO Donation Question (Radio)
| Property | Value |
|----------|-------|
| Type | `radio` |
| Name | `boolQCO` |
| Label | Dynamic: `"Have you donated to another Arizona Qualified Charitable Organization for the {year} tax year?"` |
| Help | "This does not include any public school donations, private school STO donations, only donations to an Arizona QCO in an effort to claim a tax credit." |
| Options | No, Yes |
| Validation | `required` |
| Conditional | Only shown if `filingYear` selected |

#### QCO Details (2-column grid, conditional)
Shown only if `boolQCO === 'yes'`:

| Field | Type | Validation | Notes |
|-------|------|------------|-------|
| QCO Name | `text` | `required` | |
| QCO Amount | `currency` | `required`, max validation | Max based on tax credit limit |

#### QCO Confirmation Message
Shown if `qcoAmount` entered:
> "The QCO donation amount of **${amount}** will be automatically deducted from your calculated tax credit in step 3."

---

## Step 3: Donation (3 of 4)

### Tax Credit Limits
| Year | Single/HoH/MFS | Married Filing Jointly |
|------|----------------|------------------------|
| 2024 | $470 | $938 |
| 2025 | $495 | $987 |
| 2026 | $521 | $1,039 |

### Intro Text
> "Please enter the total amount you would like to donate or use the calculator button to use the maximum tax credit available for the year selected based on your filing status."

### Fields

#### Donation Amount (2-column grid on md+)
| Property | Value |
|----------|-------|
| Type | `currency` |
| Name | `totalAmount` |
| ID | `totalAmount` |
| Label | "Donation Amount" |
| Placeholder | "Enter amount or auto calculate max" |
| Suffix Icon | `calculator` (clickable) |
| Validation | `required`, max validation |
| Max | Calculated max tax credit - QCO amount |
| Disabled | Until filing status AND filing year selected |

**Calculator Click Behavior:**
- Auto-fills the max donation amount based on filing status, year, and QCO deductions

**Max Tax Credit Display:**
- Shows "Your Max Tax Credit: ${amount}"
- If QCO exists, shows base credit and deduction

#### School Selection (Autocomplete)
| Property | Value |
|----------|-------|
| Type | `autocomplete` |
| Name | `schoolId` |
| Label | "Would you like to recommend a school?" |
| Placeholder | "Choose your school..." |
| Options | Schools from server with format `"Name (Type)"` |
| Validation | `required` |
| Open on Focus | true |
| Conditional | Only shown if `totalAmount` entered |

**School Selection Confirmation:**
- Green background (`bg-lime-50`)
- Shows selected school name and type

---

## Step 4: Payment (4 of 4)

### Card Details (Stripe Elements)
| Property | Value |
|----------|-------|
| Type | Stripe Card Element |
| ID | `card-element` |
| Container Styling | `rounded-lg border border-slate-300 bg-white p-4 shadow-sm` |
| Focus State | `border-blue-500 ring-2 ring-blue-200` |

**Stripe Element Configuration:**
```javascript
{
    style: {
        base: {
            fontSize: '16px',
            fontFamily: "'Roboto', Helvetica, Arial, Lucida, sans-serif",
            color: '#1f2937',
            '::placeholder': { color: '#9ca3af' }
        },
        invalid: {
            color: '#dc2626',
            iconColor: '#dc2626'
        }
    }
}
```

**States:**
- Loading: Shows spinner with "Loading payment form..."
- Error: Shows error message with warning icon
- Mounted: Ready for input

### Billing Address Checkbox
| Property | Value |
|----------|-------|
| Type | `checkbox` |
| Name | `billingAddressEnable` |
| Label | "My credit card address is the same as above." |
| Default Value | `true` |

### Alternate Billing Address (Conditional)
Shown if checkbox unchecked (4-column grid on md+):
| Field | Type | Validation |
|-------|------|------------|
| Address | `text` | `required` |
| City | `text` | `required` |
| State | `dropdown` | `required` |
| Zip Code | `text` (number) | `required` |

---

## Step 5: Tax Preparer Permission (Optional)

**Note:** This is labeled as "Optional" and not numbered as part of the main 4 steps.

### Fields

#### Enable Tax Preparer Contact
| Property | Value |
|----------|-------|
| Type | `checkbox` |
| Name | `taxProfessionalEnable` |
| Label | "Permission given for Tax Preparer contact." |
| Help | "This allows IBE to share the details of your donation with your listed tax preparer." |

### Tax Preparer Details (4-column grid, conditional)
Shown if checkbox checked:

| Field | Type | Validation | Grid |
|-------|------|------------|------|
| First Name | `text` | `required` | 1 col |
| Last Name | `text` | `required` | 1 col |
| Phone | `mask` | `required` | 1 col, icon: phone |
| Email | `email` | `required` | 1 col, icon: envelope |

---

## Submit Button

| Property | Value |
|----------|-------|
| Type | `submit` |
| Label | "Submit" |
| Disabled | If processing OR card not mounted |
| Full Width | Yes |
| Background | `#183762` (IBE Blue) |
| Hover | `#0f2544` (darker blue) |
| Disabled Opacity | 0.5 |

---

## Form Submission Data

The form collects and submits the following data structure:

```javascript
{
    // Step 1 - Donor Info
    filing_status: number,
    donor_title: string,
    donor_first_name: string,
    donor_last_name: string,
    donor_title2: string,           // Secondary donor if applicable
    donor_first_name2: string,
    donor_last_name2: string,
    donor_phone: string,
    donor_email: string,
    
    // Step 1 - Billing Address
    billing_street: string,
    billing_city: string,
    billing_state: string,          // 2-letter code
    billing_postal_code: string,
    billing_country: 'US',
    
    // Step 2 - Tax Info
    filing_year: number,
    qco_name: string,
    qco_amount: number,
    
    // Step 3 - Donation
    amount_cents: number,           // Amount in cents
    school_id: number,
    
    // Step 4 - Payment
    payment_method_id: string,      // Stripe payment method ID
    same_as_billing: boolean,
    mailing_street: string,
    mailing_city: string,
    mailing_state: string,
    mailing_postal_code: string,
    mailing_country: 'US',
    
    // Step 5 - Tax Preparer
    tax_professional_name: string,
    tax_professional_phone: string,
    tax_professional_email: string
}
```

---

## FormKit Styling Details

### Input Styling
```css
.formkit-input {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #94a3b8;
    border-radius: 0.25rem;
    background-color: white;
    font-family: 'Roboto', Helvetica, Arial, Lucida, sans-serif;
    font-size: 1rem;
    color: #1f2937;
}

.formkit-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}
```

### Label Styling
```css
.formkit-label {
    display: block;
    font-family: 'Roboto', Helvetica, Arial, Lucida, sans-serif;
    font-size: 0.875rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 0.25rem;
}
```

### Icon Styling
```css
.formkit-suffix-icon,
.formkit-prefix-icon {
    color: #9ca3af;
    width: 1rem;
    height: 1rem;
}

/* In step headers */
.donation-step-heading .formkit-icon svg {
    width: 1em;
}

.donation-step-heading .formkit-icon svg path {
    fill: #fff !important;
}
```

### Step Header Styling
```css
.donation-step-heading {
    transition: background-color 0.5s ease;
}

.donation-step-heading-invalid {
    background-color: #286090;
}

.donation-step-heading-valid {
    background-color: #3c3;
}

/* Applied classes when valid */
/* bg-lime-500 (#84cc16) */
```

### Repeater Add Button
```css
.formkit-addButton[data-disabled="true"] {
    display: none;
}
```

---

## Icons Used

| Icon Name | Source | Usage |
|-----------|--------|-------|
| `phone` | FontAwesome (inline SVG) | Phone input suffix |
| `envelope` | FontAwesome (inline SVG) | Email input suffix |
| `calculator` | FontAwesome (inline SVG) | Currency input suffix (clickable) |
| `credit-card` | FontAwesome (inline SVG) | Card details label |
| `circle-check` | FontAwesome (inline SVG) | Step completion indicator |
| `warning` | FontAwesome (inline SVG) | Error messages |
| `info` | FontAwesome (inline SVG) | Info messages |

---

## Validation Rules

### Built-in Validations
- `required` - Field must have a value
- `confirm` - Must match the field without "_confirm" suffix
- `email` - Valid email format

### Custom Validations
#### `telValidate` (Phone)
```javascript
const telValidate = (node) => {
    const value = node.value;
    if (!value || value === '(___) ___-____') return true;
    const digits = (value || '').replace(/\D/g, '');
    return digits.length === 0 || digits.length === 10;
};
```

---

## Third-Party Integrations

### Stripe
- **Purpose:** Payment processing
- **Key:** Loaded from `stripe_key` prop
- **Elements:** Card Element mounted in `#card-element`

### Google Analytics
- **Tracking ID:** `G-3RV71BM8Y5`

### Tawk.to
- **Purpose:** Live chat support
- **Property:** `67ca5ff7374e52190e33d1b7`

---

## Success Page

After successful submission, user is redirected to a thank you page:

**URL:** `/thankyou.html` (legacy) or success Inertia page

**Content:**
- Title: "Thank you for helping Build Bridges To Success!"
- Message: "Keep an eye out for your receipt in your email."
- Cross-promotion: Link to Arizona Private School Tax Credit at ibescholarships.org

---

## Legacy Implementation Details

### Tech Stack
- **Frontend Framework:** Vue 3
- **Form Library:** FormKit
- **Styling:** Tailwind CSS (v3) + Custom CSS
- **Payment Processing:** Stripe.js v3
- **Backend/Serving:** PHP (`index.php`) serving a Vite-built SPA

### Application Entry Point (`index.php`)
The legacy application is served via `index.php`, which performs server-side logic before loading the Vue application:
1. **School Lookup:** Checks for a `schoolId` query parameter (e.g., `?schoolId=123`).
2. **API Call:** Fetches school details from `https://app.ibefoundation.org/api/school/{schoolId}`.
3. **Dynamic Metadata:** Updates the page `<title>` and `<h1>`/`<h2>` headings with the school name if found.
4. **Mount Point:** Renders the `#app` div where the Vue application mounts.

### Asset Loading
- **Scripts:** Loads a minified JS bundle from `assets/index-*.js` (e.g., `assets/index-B3baKkks.js`).
- **Styles:** Loads a CSS bundle from `assets/index-*.css` (e.g., `assets/index-DV3nfq5R.css`).
- **Fonts:** Preconnects to Google Fonts for `Montserrat` and `Roboto`.

### Form Logic & State Management
- **State Structure:** The form state appears to be nested by steps (e.g., `step1.filingStatus`, `step2.filingYear`).
- **Computed Behaviors:**
  - `totalAmount` is reset to `null` if `filingStatus` or `filingYear` changes.
  - **Calculator Button:** When clicked, it sets `totalAmount` to the `maxTaxCreditDonation` value derived from the selected filing status and year.
- **Validation:**
  - **Phone Number:** Uses a regex `^\(\d{3}\) \d{3}-\d{4}|\d{10}$` to ensure 10 digits or a formatted string.
  - **Stripe Loading:** Includes specific error handling for Stripe.js loading failures (`STRIPE_NOT_LOADED`, `INSTANCE_NOT_DEFINED`).

### CSS & Styling
- **Tailwind:** Extensive use of utility classes (e.g., `bg-ibeBlue`, `text-slate-700`).
- **Custom Classes:**
  - `.donation-step-heading`: Base class for step headers.
  - `.donation-step-heading-invalid`: Background color `#286090`.
  - `.donation-step-heading-valid`: Background color `#3c3` (Lime Green).
  - `.formkit-addButton[data-disabled=true]`: Hides the repeater add button when limits are reached.
- **Icons:**
  - Uses FontAwesome SVGs.
  - `.donation-step-heading .formkit-icon svg path { fill: #fff !important; }` ensures icons in headers are white.

### Third-Party Scripts
- **Stripe:** `https://js.stripe.com/v3`
- **Google Analytics:** `G-3RV71BM8Y5`
- **Tawk.to:** Widget ID `67ca5ff7374e52190e33d1b7`
