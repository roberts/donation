<?php

namespace App\Data;

use App\Enums\FilingStatus;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class DonationFormData extends Data
{
    public function __construct(
        #[Required, Min(1)]
        public int $amount,

        #[Required, Max(255)]
        public string $donor_first_name,

        #[Required, Max(255)]
        public string $donor_last_name,

        #[Required, Email]
        public string $donor_email,

        #[Required]
        public AddressData $billing_address,

        #[Required]
        public int $filing_year,

        #[Required]
        public FilingStatus $filing_status,

        #[Required]
        public string $payment_method_id,

        public ?string $donor_title = null,

        public ?string $donor_spouse_title = null,

        public ?string $donor_spouse_first_name = null,

        public ?string $donor_spouse_last_name = null,

        public ?string $donor_phone = null,

        public ?AddressData $mailing_address = null,

        public ?string $qco = null,

        public ?string $school_name_snapshot = null,

        public ?string $tax_professional_name = null,

        public ?string $tax_professional_phone = null,

        public ?string $tax_professional_email = null,

        public ?int $school_id = null,

        public ?string $custom_school = null,
    ) {}
}
