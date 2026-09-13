<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'code' => Str::upper(fake()->unique()->bothify('CMP-###')),
            'name' => $name,
            'legal_name' => 'PT '.$name,
            'tax_id' => fake()->numerify('##.###.###.#-###.###'),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('02#-####-####'),
            'website' => fake()->url(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'ID',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the company is no longer operating.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
