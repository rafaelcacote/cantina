<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\School;
use App\Models\Student;
use App\Models\TabEntry;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseAuthorization>
 */
class PurchaseAuthorizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'order_id' => Order::factory(),
            'tab_entry_id' => null,
            'authorization_type' => 'tab_confirmation',
            'auth_method' => 'pin',
            'success' => true,
            'failure_reason' => null,
            'device_type' => 'terminal',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_by' => User::factory(),
        ];
    }
}
