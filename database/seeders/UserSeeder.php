<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1 Admin cố định — dễ login khi dev
        User::create([
            'name' => 'Admin',
            'email' => 'admin@dksc.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'referral_code' => 'ADMIN0001',
            'email_verified_at' => now(),
        ]);

        // 10 customers với faker
        User::factory()->count(10)->create();
    }
}
