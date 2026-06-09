<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['create', 'update', 'delete']),
            'entity_type' => fake()->randomElement(['order', 'student', 'wallet_transaction']),
            'entity_id' => fake()->numberBetween(1, 9999),
            'old_data' => null,
            'new_data' => ['sample' => true],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
