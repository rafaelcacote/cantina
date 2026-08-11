<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['slug' => 'basico'],
            [
                'name' => 'Plano Basico',
                'price' => 99.90,
                'billing_cycle' => 'monthly',
                'max_students' => 500,
                'max_users' => 20,
                'features' => [
                    'reports' => true,
                    'mobile_app' => true,
                    'parental_control' => true,
                ],
                'active' => true,
            ]
        );
    }
}
