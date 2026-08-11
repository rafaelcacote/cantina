<?php

namespace Database\Factories;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentInvitation>
 */
class StudentInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_id' => Student::factory(),
            'parent_id' => ParentGuardian::factory(),
            'token' => Str::random(40),
            'expires_at' => now()->addDays(14),
            'used_at' => null,
        ];
    }
}
