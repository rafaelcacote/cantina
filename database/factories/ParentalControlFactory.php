<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParentalControl>
 */
class ParentalControlFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_id' => Student::factory(),
            'enabled' => true,
            'control_mode' => 'partial',
            'daily_spending_limit' => 30.00,
            'weekly_spending_limit' => 120.00,
            'allow_tab_usage' => true,
            'allow_wallet_usage' => true,
            'allow_convenience_access' => false,
            'allow_snack_access' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
