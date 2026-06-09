<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'student_id' => Student::factory(),
            'notification_type' => 'info',
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(),
            'payload' => null,
            'read_at' => null,
        ];
    }
}
