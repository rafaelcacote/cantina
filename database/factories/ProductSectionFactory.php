<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductSection>
 */
class ProductSectionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Lanches', 'Conveniência', 'Bebidas']);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'active' => true,
        ];
    }
}
