<?php

namespace Database\Factories;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_id' => Student::factory(),
            'parent_id' => ParentGuardian::factory(),
            'amount' => fake()->randomFloat(2, 5, 100),
            'payment_method' => fake()->randomElement(['pix', 'cash', 'card']),
            'reference' => fake()->optional()->bothify('PAY-####'),
            'status' => 'completed',
            'paid_at' => now(),
            'created_by' => User::factory(),
        ];
    }
}
