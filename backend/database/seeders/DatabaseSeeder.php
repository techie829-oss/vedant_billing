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
            GstStateSeeder::class,
            ExactInvoiceSeeder::class, // Creates User ("admin@rschitra.com") and Business ("R/S CHITRA...")
            ChartOfAccountsSeeder::class, // Needs Business to exist
        ]);
    }
}
