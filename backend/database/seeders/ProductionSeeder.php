<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\InUser;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's database for production.
     */
    public function run(): void
    {
        // 1. Seed Essential Configuration Data
        $this->call([
            PlanSeeder::class,
            GstStateSeeder::class,
            ChartOfAccountsSeeder::class,
        ]);

        // 2. Create Admin User (Protected by Env Vars)
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            $user = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make($adminPassword),
                ]
            );

            // Create Internal Profile for Admin
            InUser::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'access_level' => 'super_admin',
                    'status' => 'active',
                ]
            );

            $this->command->info("✅ Production Admin User Created: {$adminEmail}");
        } else {
            $this->command->warn('⚠️  ADMIN_EMAIL or ADMIN_PASSWORD not set in .env. Admin user skipped.');
        }
    }
}
