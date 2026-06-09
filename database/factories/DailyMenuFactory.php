<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyMenu>
 */
class DailyMenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'school_id' => School::factory(),
            'menu_date' => now()->toDateString(),
            'title' => 'Cardápio do dia',
            'description' => fake()->optional()->sentence(),
            'active' => true,
        ];
    }
}
