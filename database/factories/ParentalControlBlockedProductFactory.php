<?php

namespace Database\Factories;

use App\Models\ParentalControl;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParentalControlBlockedProduct>
 */
class ParentalControlBlockedProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'parental_control_id' => ParentalControl::factory(),
            'product_id' => Product::factory(),
        ];
    }
}
