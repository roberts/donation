<?php

namespace Database\Factories;

use App\Enums\SchoolType;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ibe_id' => fake()->boolean(70) ? fake()->unique()->numberBetween(1, 100000) : null,
            'name' => fake()->company().' '.fake()->randomElement(['School', 'Academy', 'Preparatory', 'Institute', 'Learning Center']),
            'type' => fake()->randomElement(SchoolType::cases()),
        ];
    }

    /**
     * Indicate that the school is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SchoolType::Public,
        ]);
    }

    /**
     * Indicate that the school is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SchoolType::Private,
        ]);
    }

    /**
     * Indicate that the school is a charter school.
     */
    public function charter(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => SchoolType::Charter,
        ]);
    }
}
