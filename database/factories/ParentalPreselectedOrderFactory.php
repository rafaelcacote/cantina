<?php

namespace Database\Factories;

use App\Models\ParentGuardian;
use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParentalPreselectedOrder>
 */
class ParentalPreselectedOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'school_id' => School::factory(),
            'parent_id' => ParentGuardian::factory(),
            'student_id' => Student::factory(),
            'order_date' => now()->toDateString(),
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
