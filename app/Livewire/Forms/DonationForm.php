<?php

namespace App\Livewire\Forms;

use App\Data\AddressData;
use App\Data\DonationFormData;
use App\Enums\FilingStatus;
use Livewire\Attributes\Validate;
use Livewire\Form;

class DonationForm extends Form
{
    // Step 1
    #[Validate('required', message: 'Filing Status is required')]
    public string $filingStatus = '';

    /** @var array<int, array<string, string>> */
    public array $donors = [
        [
            'title' => '',
            'first_name' => '',
            'last_name' => '',
        ],
    ];

    #[Validate('required', message: 'Phone number is required')]
    #[Validate('regex:/^(\(\d{3}\) \d{3}-\d{4}|\d{10})$/', message: 'Please enter a valid phone number')]
    public string $phone = '';

    #[Validate('required', message: 'Street address is required')]
    public string $address = '';

    #[Validate('required', message: 'City is required')]
    public string $city = '';

    #[Validate('required', message: 'State is required')]
    public string $state = '';

    #[Validate('required', message: 'Zip code is required')]
    public string $zip = '';

    #[Validate('required|email', message: ['required' => 'Valid email is required', 'email' => 'Valid email is required'])]
    public string $email = '';

    #[Validate('required|email|same:email', message: ['required' => 'Valid email is required', 'email' => 'Valid email is required', 'same' => 'Emails must match'])]
    public string $email_confirmation = '';

    // Step 2
    #[Validate('required', message: 'Please select a tax year')]
    public string $filingYear = '';

    #[Validate('required', message: 'Please answer the QCO question')]
    public string $boolQCO = '';

    public string $qcoName = '';

    public string $qcoAmount = '';

    // Step 3
    #[Validate('required', message: 'Donation amount is required')]
    #[Validate('numeric', message: 'Donation amount must be a number')]
    #[Validate('min:5', message: 'Donation amount must be at least $5')]
    public string $totalAmount = '';

    public string $schoolId = '';

    public string $customSchool = '';

    // Step 4
    public bool $billingAddressEnable = true;

    public string $billingAddress = '';

    public string $billingCity = '';

    public string $billingState = '';

    public string $billingZip = '';

    #[Validate('required', message: 'Payment method is required')]
    public string $paymentMethodId = '';

    // Step 5
    public bool $taxProfessionalEnable = false;

    public string $taxProfessionalFirstName = '';

    public string $taxProfessionalLastName = '';

    public string $taxProfessionalPhone = '';

    public string $taxProfessionalEmail = '';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'donors.*.first_name' => 'required',
            'donors.*.last_name' => 'required',
            'qcoName' => 'required_if:boolQCO,yes',
            'qcoAmount' => 'required_if:boolQCO,yes|numeric|min:0',
            'billingAddress' => 'required_if:billingAddressEnable,false',
            'billingCity' => 'required_if:billingAddressEnable,false',
            'billingState' => 'required_if:billingAddressEnable,false',
            'billingZip' => 'required_if:billingAddressEnable,false',
            'taxProfessionalFirstName' => 'nullable',
            'taxProfessionalLastName' => 'nullable',
            'taxProfessionalPhone' => 'nullable',
            'taxProfessionalEmail' => 'nullable|email',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'donors.*.first_name.required' => 'First name is required',
            'donors.*.last_name.required' => 'Last name is required',
            'qcoName.required_if' => 'QCO Name is required',
            'qcoAmount.required_if' => 'QCO Amount is required',
            'billingAddress.required_if' => 'Billing Address is required',
            'billingCity.required_if' => 'Billing City is required',
            'billingState.required_if' => 'Billing State is required',
            'billingZip.required_if' => 'Billing Zip is required',
            'taxProfessionalFirstName.required_if' => 'First Name is required',
            'taxProfessionalLastName.required_if' => 'Last Name is required',
            'taxProfessionalPhone.required_if' => 'Phone is required',
            'taxProfessionalEmail.required_if' => 'Email is required',
        ];
    }

    public function adjustDonorsForFilingStatus(): void
    {
        // If Married Filing Jointly (1), allow 2 donors. Otherwise 1.
        if ($this->filingStatus != FilingStatus::MarriedFilingJointly->value) {
            // Reset to 1 donor if not married filing jointly
            $this->donors = array_slice($this->donors, 0, 1);
        }
    }

    public function isStep1Valid(): bool
    {
        return $this->filingStatus &&
               $this->phone &&
               $this->address &&
               $this->city &&
               $this->state &&
               $this->zip &&
               $this->email &&
               $this->email_confirmation &&
               $this->email === $this->email_confirmation &&
               collect($this->donors)->every(fn ($d) => ! empty($d['first_name']) && ! empty($d['last_name']));
    }

    public function isStep2Valid(): bool
    {
        if (! $this->filingYear || ! $this->boolQCO) {
            return false;
        }
        if ($this->boolQCO === 'yes') {
            return $this->qcoName && $this->qcoAmount !== '';
        }

        return true;
    }

    public function isStep3Valid(): bool
    {
        return ((float) $this->totalAmount) > 0;
    }

    public function toDTO(string $paymentMethodId, ?string $schoolName): DonationFormData
    {
        $mailingAddress = new AddressData(
            street: $this->address,
            street_line_2: null,
            city: $this->city,
            state: $this->state,
            postal_code: $this->zip,
            country: 'US',
        );

        $billingStreet = $this->billingAddressEnable ? $this->address : $this->billingAddress;
        $billingCity = $this->billingAddressEnable ? $this->city : $this->billingCity;
        $billingState = $this->billingAddressEnable ? $this->state : $this->billingState;
        $billingZip = $this->billingAddressEnable ? $this->zip : $this->billingZip;

        $billingAddress = new AddressData(
            street: $billingStreet,
            street_line_2: null,
            city: $billingCity,
            state: $billingState,
            postal_code: $billingZip,
            country: 'US',
        );

        return new DonationFormData(
            donor_email: $this->email,
            donor_first_name: $this->donors[0]['first_name'],
            donor_last_name: $this->donors[0]['last_name'],
            donor_title: $this->donors[0]['title'],
            donor_spouse_title: $this->donors[1]['title'] ?? null,
            donor_spouse_first_name: $this->donors[1]['first_name'] ?? null,
            donor_spouse_last_name: $this->donors[1]['last_name'] ?? null,
            donor_phone: $this->phone,
            mailing_address: $mailingAddress,
            billing_address: $billingAddress,
            school_id: $this->schoolId ? (int) $this->schoolId : null,
            custom_school: $this->customSchool ?: null,
            amount: (int) (((float) $this->totalAmount) * 100),
            filing_year: (int) $this->filingYear,
            filing_status: FilingStatus::from($this->filingStatus),
            qco: $this->boolQCO === 'yes' ? json_encode(['name' => $this->qcoName, 'amount' => $this->qcoAmount]) : null,
            school_name_snapshot: $schoolName,
            tax_professional_name: $this->taxProfessionalEnable ? ($this->taxProfessionalFirstName.' '.$this->taxProfessionalLastName) : null,
            tax_professional_phone: $this->taxProfessionalEnable ? $this->taxProfessionalPhone : null,
            tax_professional_email: $this->taxProfessionalEnable ? $this->taxProfessionalEmail : null,
            payment_method_id: $paymentMethodId,
        );
    }
}
