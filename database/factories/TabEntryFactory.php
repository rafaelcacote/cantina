<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Student;
use App\Models\StudentTab;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TabEntry>
 */
class TabEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_tab_id' => StudentTab::factory(),
            'student_id' => Student::factory(),
            'order_id' => Order::factory(),
            'amount' => fake()->randomFloat(2, 1, 30),
            'description' => fake()->optional()->sentence(),
            'entry_date' => now()->toDateString(),
            'status' => 'open',
            'authorized_by_pin' => false,
            'authorization_method' => null,
            'authorized_at' => null,
            'created_by' => User::factory(),
        ];
    }
}
