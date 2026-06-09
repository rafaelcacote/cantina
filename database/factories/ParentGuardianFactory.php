<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParentGuardian>
 */
class ParentGuardianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'cpf' => fake()->optional()->numerify('###########'),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
        ];
    }
}
