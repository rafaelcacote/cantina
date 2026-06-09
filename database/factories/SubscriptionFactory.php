<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => now()->addDays(14),
            'next_billing_at' => now()->addMonth(),
            'cancelled_at' => null,
        ];
    }
}
