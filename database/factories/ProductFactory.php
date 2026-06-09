<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\ProductSection;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'section_id' => ProductSection::factory(),
            'category_id' => ProductCategory::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'sku' => fake()->optional()->bothify('SKU-####'),
            'barcode' => fake()->optional()->ean13(),
            'product_type' => 'resale',
            'sale_type' => 'unit',
            'price' => fake()->randomFloat(2, 2, 30),
            'cost_price' => fake()->optional()->randomFloat(2, 1, 20),
            'image_url' => null,
            'active' => true,
            'visible_in_app' => true,
            'allow_custom_request' => false,
            'requires_preparation' => false,
            'stock_controlled' => true,
            'minimum_stock_alert' => 5,
        ];
    }
}
