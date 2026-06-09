<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(0, 100),
            'reserved_quantity' => 0,
        ];
    }
}
