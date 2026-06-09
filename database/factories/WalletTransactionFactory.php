<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentWallet;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'wallet_id' => StudentWallet::factory(),
            'student_id' => Student::factory(),
            'transaction_type' => fake()->randomElement(['topup', 'purchase', 'refund', 'adjustment', 'reversal']),
            'amount' => fake()->randomFloat(2, 1, 50),
            'balance_before' => 0,
            'balance_after' => 0,
            'reference_type' => null,
            'reference_id' => null,
            'description' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
