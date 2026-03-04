<?php

namespace Database\Factories;

use App\Enums\DonationStatus;
use App\Enums\FilingStatus;
use App\Enums\PaymentMethod;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    protected $model = Donation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $school = School::factory();
        $donor = Donor::factory();

        return [
            'school_id' => $school,
            'donor_id' => $donor,
            'payment_method' => PaymentMethod::Card,
            'amount' => fake()->numberBetween(1000, 100000), // $10 - $1000
            'status' => DonationStatus::Paid,
            'filing_year' => fake()->numberBetween(date('Y') - 1, date('Y')),
            'filing_status' => fake()->randomElement(FilingStatus::cases()),
            'qco' => fake()->optional()->regexify('[A-Z]{3}[0-9]{3}'),
            'school_name_snapshot' => fn (array $attributes) => School::find($attributes['school_id'])?->name ?? fake()->company(),
            'tax_professional_name' => fake()->optional(0.3)->name(),
            'tax_professional_phone' => fake()->optional(0.3)->numerify('(###) ###-####'),
            'tax_professional_email' => fake()->optional(0.3)->safeEmail(),
            'receipt_sent_at' => null,
        ];
    }

    /**
     * Indicate that the donation is from a couple.
     */
    public function couple(): static
    {
        return $this->state(fn (array $attributes) => [
            'donor_spouse_title' => fake()->title(),
            'donor_spouse_first_name' => fake()->firstName(),
            'donor_spouse_last_name' => fake()->lastName(),
            'filing_status' => FilingStatus::MarriedFilingJointly,
        ]);
    }

    /**
     * Indicate that the receipt has been sent.
     */
    public function receiptSent(): static
    {
        return $this->state(fn (array $attributes) => [
            'receipt_sent_at' => now(),
        ]);
    }

    /**
     * Configure the donation for a specific school.
     */
    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes) => [
            'school_id' => $school->id,
            'school_name_snapshot' => $school->name,
        ]);
    }
}
