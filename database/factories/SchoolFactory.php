<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'Escola '.fake()->company(),
            'document' => fake()->optional()->numerify('##############'),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'address' => fake()->optional()->address(),
            'active' => true,
        ];
    }
}
