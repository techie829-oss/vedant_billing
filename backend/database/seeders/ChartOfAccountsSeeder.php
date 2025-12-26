<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Ledger;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $business = Business::first();

        if (!$business) {
            $this->command->info('No business found. Seeder skipped.');
            return;
        }

        $this->createDefaultAccounts($business);
    }

    public function createDefaultAccounts(Business $business)
    {
        $accounts = [
            // Assets (1000-1999)
            ['code' => '1010', 'name' => 'Cash', 'type' => 'asset', 'is_system' => true],
            ['code' => '1020', 'name' => 'Accounts Receivable', 'type' => 'asset', 'is_system' => true],
            ['code' => '1030', 'name' => 'Inventory Asset', 'type' => 'asset', 'is_system' => false],

            // Liabilities (2000-2999)
            ['code' => '2010', 'name' => 'Accounts Payable', 'type' => 'liability', 'is_system' => true],
            ['code' => '2020', 'name' => 'Sales Tax Payable', 'type' => 'liability', 'is_system' => true],

            // Equity (3000-3999)
            ['code' => '3010', 'name' => 'Owner\'s Equity', 'type' => 'equity', 'is_system' => false],
            ['code' => '3020', 'name' => 'Retained Earnings', 'type' => 'equity', 'is_system' => true],

            // Revenue (4000-4999)
            ['code' => '4010', 'name' => 'Sales Revenue', 'type' => 'revenue', 'is_system' => true],
            ['code' => '4020', 'name' => 'Service Revenue', 'type' => 'revenue', 'is_system' => false],

            // Expenses (5000-5999)
            ['code' => '5010', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'is_system' => true],
            ['code' => '5020', 'name' => 'Rent Expense', 'type' => 'expense', 'is_system' => false],
            ['code' => '5030', 'name' => 'Salaries Expense', 'type' => 'expense', 'is_system' => false],
            ['code' => '5040', 'name' => 'Office Supplies', 'type' => 'expense', 'is_system' => false],
        ];

        foreach ($accounts as $account) {
            Ledger::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'code' => $account['code'],
                ],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'is_system' => $account['is_system'],
                    'description' => 'Default ' . $account['name'] . ' account',
                ]
            );
        }

        $this->command->info('Default chart of accounts created for ' . $business->name);
    }
}
