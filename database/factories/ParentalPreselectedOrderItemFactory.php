<?php

namespace Database\Factories;

use App\Models\ParentalPreselectedOrder;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParentalPreselectedOrderItem>
 */
class ParentalPreselectedOrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'parental_preselected_order_id' => ParentalPreselectedOrder::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 3),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
