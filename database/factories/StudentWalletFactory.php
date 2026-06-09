<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentWallet>
 */
class StudentWalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_id' => Student::factory(),
            'balance' => fake()->randomFloat(2, 0, 200),
            'credit_limit' => fake()->randomFloat(2, 0, 100),
            'allow_negative_balance' => false,
        ];
    }
}
