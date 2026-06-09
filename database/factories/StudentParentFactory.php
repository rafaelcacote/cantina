<?php

namespace Database\Factories;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentParent>
 */
class StudentParentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_id' => Student::factory(),
            'parent_id' => ParentGuardian::factory(),
            'relationship_type' => fake()->randomElement(['mother', 'father', 'guardian']),
            'is_primary' => true,
            'financial_responsible' => true,
        ];
    }
}
