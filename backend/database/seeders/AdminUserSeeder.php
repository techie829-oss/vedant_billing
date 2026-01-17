<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\InUser;
use App\Enums\InternalRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Base User
        $user = User::firstOrCreate(
            ['email' => 'admin@vedantbilling.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Create Internal Profile
        InUser::firstOrCreate(
            ['user_id' => $user->id],
            [
                'access_level' => 'super_admin', // Or use InUser::LEVEL_SUPER_ADMIN if preferred
                'status' => 'active',
            ]
        );

        $this->command->info('✅ Admin User Created: admin@vedantbilling.com / password');
    }
}
