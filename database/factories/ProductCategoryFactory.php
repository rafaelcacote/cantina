<?php

namespace Database\Factories;

use App\Models\ProductSection;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Sanduíches', 'Sucos', 'Doces']);

        return [
            'tenant_id' => Tenant::factory(),
            'section_id' => ProductSection::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'active' => true,
        ];
    }
}
