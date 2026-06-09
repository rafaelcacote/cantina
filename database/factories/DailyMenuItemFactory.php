<?php

namespace Database\Factories;

use App\Models\DailyMenu;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMenuItem>
 */
class DailyMenuItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'daily_menu_id' => DailyMenu::factory(),
            'product_id' => Product::factory(),
            'planned_quantity' => fake()->optional()->numberBetween(10, 100),
            'available_quantity' => fake()->optional()->numberBetween(5, 100),
            'price_override' => null,
            'sort_order' => 0,
            'active' => true,
        ];
    }
}
