<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $unit = fake()->randomFloat(2, 2, 20);
        $qty = fake()->numberBetween(1, 3);

        return [
            'tenant_id' => Tenant::factory(),
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'item_name_snapshot' => fake()->words(2, true),
            'unit_price' => $unit,
            'quantity' => $qty,
            'total_price' => $unit * $qty,
            'observation' => null,
            'custom_request_text' => null,
            'item_status' => 'pending',
        ];
    }
}
