<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        $previous = fake()->numberBetween(0, 100);
        $qty = fake()->numberBetween(1, 20);
        $new = $previous + $qty;

        return [
            'tenant_id' => Tenant::factory(),
            'product_id' => Product::factory(),
            'movement_type' => fake()->randomElement(['in', 'out', 'adjustment', 'loss', 'production']),
            'quantity' => $qty,
            'previous_quantity' => $previous,
            'new_quantity' => $new,
            'reference_type' => null,
            'reference_id' => null,
            'description' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
