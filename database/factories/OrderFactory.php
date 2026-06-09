<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'parent_id' => null,
            'placed_by_user_id' => User::factory(),
            'order_channel' => 'app',
            'order_type' => 'immediate',
            'status' => 'pending',
            'payment_mode' => 'wallet',
            'total_amount' => 10.00,
            'discount_amount' => 0,
            'final_amount' => 10.00,
            'scheduled_for' => null,
            'notes' => null,
        ];
    }
}
