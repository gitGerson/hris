<?php

namespace Database\Factories;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\Religion;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_number' => Employee::generateEmployeeNumber(),
            'name' => fake()->name(),
            'phone' => '08'.fake()->unique()->numerify('##########'),
            'national_id' => fake()->unique()->numerify('################'),
            'gender' => fake()->randomElement(Gender::cases()),
            'religion' => fake()->randomElement(Religion::cases()),
            'marital_status' => fake()->randomElement(MaritalStatus::cases()),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'identity_address' => fake()->streetAddress(),
            'domicile_address' => fake()->streetAddress(),
            'branch_id' => Branch::factory(),
            'department_id' => Department::factory(),
            'position_id' => Position::factory(),
            'job_level_id' => JobLevel::factory(),
            'join_date' => fake()->dateTimeBetween('-5 years')->format('Y-m-d'),
            'employment_status' => EmploymentStatus::Active,
        ];
    }

    /**
     * Indicate that the employee has left.
     */
    public function resigned(): static
    {
        return $this->state(fn (array $attributes): array => [
            'employment_status' => EmploymentStatus::Resigned,
            'termination_date' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
        ]);
    }
}
