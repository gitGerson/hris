<?php

namespace Database\Factories;

use App\Models\JobLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobLevel>
 */
class JobLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('LVL-##')),
            'name' => fake()->unique()->jobTitle(),
            /** Unique per row: the column has a unique index. */
            'level' => fake()->unique()->numberBetween(1, 255),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the level is no longer in use.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
