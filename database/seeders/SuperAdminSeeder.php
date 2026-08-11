<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@cantina.local'],
            [
                'tenant_id' => null,
                'name' => 'Super Admin',
                'phone' => '(11) 90000-0001',
                'cpf' => '00000000000',
                'user_type' => 'super_admin',
                'active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
    }
}
