<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentTab>
 */
class StudentTabFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_id' => Student::factory(),
            'current_balance' => fake()->randomFloat(2, 0, 150),
            'billing_cycle_type' => 'monthly',
            'due_day' => fake()->optional()->numberBetween(1, 28),
            'active' => true,
        ];
    }
}
