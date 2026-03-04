<?php

namespace Database\Factories;

use App\Models\Donor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'notable_type' => Donor::class,
            'notable_id' => Donor::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
