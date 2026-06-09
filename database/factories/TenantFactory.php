<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'document' => fake()->optional()->numerify('##############'),
            'email' => fake()->optional()->companyEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'logo_url' => fake()->optional()->imageUrl(),
            'status' => 'active',
            'trial_ends_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'subscription_ends_at' => fake()->optional()->dateTimeBetween('+1 month', '+1 year'),
        ];
    }
}
