<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\Feature;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Features
        $features = [
            [
                'name' => 'Monthly Invoices',
                'slug' => 'monthly_invoices',
                'type' => 'limit',
                'default_limit' => 10,
                'description' => 'Number of invoices you can generate per month.',
            ],
            [
                'name' => 'Clients Limit',
                'slug' => 'clients_limit',
                'type' => 'limit',
                'default_limit' => 5,
                'description' => 'Maximum number of clients/customers you can manage.',
            ],
            [
                'name' => 'Multi-User Access',
                'slug' => 'multi_user',
                'type' => 'boolean',
                'default_limit' => 0, // 0 = False
                'description' => 'Allow multiple team members to access the dashboard.',
            ],
            [
                'name' => 'API Access',
                'slug' => 'api_access',
                'type' => 'boolean',
                'default_limit' => 0,
                'description' => 'Access to developer API.',
            ],
            [
                'name' => 'E-Way Bill / Transport Details',
                'slug' => 'eway_bill_access',
                'type' => 'boolean',
                'default_limit' => 0,
                'description' => 'Add transport details like E-Way Bill No, Vehicle No to invoices.',
            ],
            [
                'name' => 'Premium Layouts',
                'slug' => 'premium_layout_access',
                'type' => 'boolean',
                'default_limit' => 0,
                'description' => 'Access to professional and premium invoice templates.',
            ],
        ];

        $featureModels = [];
        foreach ($features as $f) {
            $featureModels[$f['slug']] = Feature::firstOrCreate(
                ['slug' => $f['slug']],
                $f
            );
        }

        // 2. Create Plans
        $plans = [
            [
                'name' => 'Free Tier',
                'slug' => 'free',
                'price' => 0,
                'interval' => 'monthly',
                'description' => 'Perfect for freelancers just starting out.',
                'status' => 'active',
                'features' => [
                    'monthly_invoices' => 10,
                    'clients_limit' => 5,
                    'multi_user' => 0,
                    'api_access' => 0,
                    'eway_bill_access' => 0,
                    'premium_layout_access' => 0,
                ]
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price' => 15,
                'interval' => 'monthly',
                'description' => 'For growing small businesses.',
                'status' => 'active',
                'features' => [
                    'monthly_invoices' => 50,
                    'clients_limit' => 20,
                    'multi_user' => 0,
                    'api_access' => 0,
                    'eway_bill_access' => 1,
                    'premium_layout_access' => 1,  // All paid plans get premium layouts
                ]
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 49,
                'interval' => 'monthly',
                'description' => 'Everything you need for your agency.',
                'status' => 'active',
                'features' => [
                    'monthly_invoices' => 5000,
                    'clients_limit' => 500,
                    'multi_user' => 1,
                    'api_access' => 0,
                    'eway_bill_access' => 1,
                    'premium_layout_access' => 1,
                ]
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price' => 199,
                'interval' => 'monthly',
                'description' => 'Maximum power and custom integrations.',
                'status' => 'active',
                'features' => [
                    'monthly_invoices' => -1,
                    'clients_limit' => 5000,
                    'multi_user' => 1,
                    'api_access' => 1,
                    'eway_bill_access' => 1,
                    'premium_layout_access' => 1,
                ]
            ],
            [
                'name' => 'Enterprise Business',
                'slug' => 'enterprise_business',
                'price' => 5000,
                'interval' => 'yearly',
                'description' => 'Maximum power and custom integrations.',
                'status' => 'active',
                'features' => [
                    'monthly_invoices' => -1,
                    'clients_limit' => -1,
                    'multi_user' => 1,
                    'api_access' => 1,
                    'eway_bill_access' => 1,
                    'premium_layout_access' => 1,
                ]
            ],
        ];

        foreach ($plans as $p) {
            $planFeatures = $p['features'];
            unset($p['features']); // Remove from plan data

            $plan = Plan::firstOrCreate(
                ['slug' => $p['slug']],
                $p
            );

            // Sync features
            foreach ($planFeatures as $slug => $limit) {
                if (isset($featureModels[$slug])) {
                    $plan->features()->syncWithoutDetaching([
                        $featureModels[$slug]->id => ['limit' => $limit]
                    ]);
                }
            }
        }
    }
}
