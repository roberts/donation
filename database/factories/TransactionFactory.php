<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Models\Donation;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'donation_id' => Donation::factory(),
            'payment_intent_id' => 'pi_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'amount' => fake()->numberBetween(1000, 100000),
            'status' => TransactionStatus::Succeeded,
            'livemode' => true,
            'payload' => [
                'id' => 'evt_'.fake()->regexify('[A-Za-z0-9]{24}'),
                'type' => 'payment_intent.succeeded',
            ],
        ];
    }

    /**
     * Indicate that the transaction succeeded.
     */
    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Succeeded,
        ]);
    }

    /**
     * Indicate that the transaction failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Failed,
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Pending,
        ]);
    }

    /**
     * Indicate that the transaction was refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Refunded,
        ]);
    }

    /**
     * Indicate that the transaction is in test mode.
     */
    public function testMode(): static
    {
        return $this->state(fn (array $attributes) => [
            'livemode' => false,
        ]);
    }

    /**
     * Configure the transaction for a specific donation.
     */
    public function forDonation(Donation $donation): static
    {
        return $this->state(fn (array $attributes) => [
            'donation_id' => $donation->id,
            'payment_intent_id' => 'pi_'.fake()->regexify('[A-Za-z0-9]{24}'),
            'amount' => $donation->amount,
        ]);
    }
}
