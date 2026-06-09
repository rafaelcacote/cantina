<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Basico', 'Pro', 'Enterprise']),
            'slug' => fake()->unique()->slug(),
            'price' => fake()->randomFloat(2, 0, 999),
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'max_students' => fake()->optional()->numberBetween(100, 3000),
            'max_users' => fake()->optional()->numberBetween(5, 100),
            'features' => [
                'reports' => true,
                'mobile_app' => true,
            ],
            'active' => true,
        ];
    }
}
