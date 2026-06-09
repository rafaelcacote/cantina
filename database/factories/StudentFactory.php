<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'school_id' => School::factory(),
            'user_id' => null,
            'enrollment_number' => fake()->optional()->unique()->numerify('MAT####'),
            'name' => fake()->name(),
            'birth_date' => fake()->optional()->date(),
            'grade' => fake()->optional()->randomElement(['1o Ano', '2o Ano', '3o Ano']),
            'classroom' => fake()->optional()->randomElement(['A', 'B', 'C']),
            'shift' => fake()->optional()->randomElement(['morning', 'afternoon']),
            'status' => 'active',
            'photo_url' => null,
            'personal_pin_hash' => null,
            'can_buy_on_credit' => false,
            'can_buy_on_tab' => false,
            'convenience_access' => false,
            'snack_access' => true,
        ];
    }
}
