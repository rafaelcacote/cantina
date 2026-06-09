<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantInvitation>
 */
class TenantInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'token' => Str::uuid()->toString(),
            'type' => 'parent_registration',
            'expires_at' => now()->addDays(7),
            'max_uses' => 1,
            'used_count' => 0,
            'active' => true,
            'created_by' => User::factory(),
        ];
    }
}
