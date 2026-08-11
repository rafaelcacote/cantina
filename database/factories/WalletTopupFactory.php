<?php

namespace Database\Factories;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\WalletTopup;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WalletTopup>
 */
class WalletTopupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'student_id' => Student::factory(),
            'parent_id' => ParentGuardian::factory(),
            'code' => strtoupper(Str::random(4)),
            'amount' => 50,
            'pix_key' => '00000000000191',
            'status' => WalletTopup::STATUS_AWAITING_PAYMENT,
        ];
    }
}
