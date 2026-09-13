<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => Str::upper(fake()->unique()->bothify('BR-###')),
            'name' => fake()->city().' Branch',
            'type' => 'outlet',
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('02#-####-####'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'timezone' => 'Asia/Jakarta',
            'latitude' => fake()->latitude(-8, -6),
            'longitude' => fake()->longitude(106, 113),
            'geofence_radius' => 100,
            'opened_on' => fake()->dateTimeBetween('-5 years')->format('Y-m-d'),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the branch is the company head office.
     */
    public function headOffice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'head_office',
            'name' => 'Head Office',
        ]);
    }

    /**
     * Indicate that the branch is closed.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
