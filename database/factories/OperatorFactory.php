<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operator>
 */
class OperatorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'school_id' => School::factory(),
            'user_id' => User::factory(),
            'role' => 'operator',
        ];
    }
}
