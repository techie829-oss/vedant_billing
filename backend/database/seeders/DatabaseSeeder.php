<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            PlanSeeder::class,
            GstStateSeeder::class, // Ensure GST states are seeded
            ChartOfAccountsSeeder::class, // Ensure Chart of Accounts is seeded
            BusinessSeeder::class,
        ]);
    }
}
