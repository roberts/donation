<?php

namespace Database\Factories;

use App\Models\Donor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donor>
 */
class DonorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'title' => fake()->optional()->title(),
            'spouse_title' => fake()->optional()->title(),
            'spouse_first_name' => fake()->optional()->firstName(),
            'spouse_last_name' => fake()->optional()->lastName(),
            'user_id' => User::factory(),
        ];
    }
}
