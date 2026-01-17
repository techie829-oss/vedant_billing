<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a Test User (Owner)
        $user = User::firstOrCreate(
            ['email' => 'test@vedantbilling.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Define Businesses to Create
        $businesses = [
            [
                'name' => 'Acme Corp',
                'slug' => 'acme-corp',
                'plan' => 'pro',
                'email' => 'info@acmecorp.com',
                'currency' => 'INR',
                'meta' => [
                    'address' => '123 Business St, Tech City',
                    'phone' => '9876543210'
                ]
            ],
            [
                'name' => 'Startup Inc',
                'slug' => 'startup-inc',
                'plan' => 'starter',
                'email' => 'hello@startup.io',
                'currency' => 'USD',
                'meta' => [
                    'address' => '456 Garage Lane, Valley',
                    'phone' => '1231231234'
                ]
            ],
            [
                'name' => 'Freelance Joe',
                'slug' => 'freelance-joe',
                'plan' => 'free',
                'email' => 'joe@freelance.net',
                'currency' => 'EUR',
                'meta' => [
                    'address' => '789 Home Office, Remote',
                    'phone' => '5555555555'
                ]
            ],
            [
                'name' => 'Global Ent',
                'slug' => 'global-ent',
                'plan' => 'enterprise',
                'email' => 'contact@global.com',
                'currency' => 'USD',
                'meta' => [
                    'address' => '1 World Plaza, NY',
                    'phone' => '9998887777'
                ]
            ]
        ];

        foreach ($businesses as $b) {
            $business = Business::firstOrCreate(
                ['slug' => $b['slug']],
                [
                    'name' => $b['name'],
                    'status' => 'active',
                    'meta' => [
                        'email' => $b['email'],
                        'currency' => $b['currency'],
                        'address' => $b['meta']['address'],
                        'phone' => $b['meta']['phone']
                    ]
                ]
            );

            // 3. Attach User to Business
            if (!$user->businesses()->where('business_id', $business->id)->exists()) {
                $user->businesses()->attach($business->id, ['role' => 'owner', 'status' => 'active']);
            }

            // 4. Create Subscription
            $plan = Plan::where('slug', $b['plan'])->first();

            if ($plan && !$business->subscriptions()->exists()) {
                Subscription::create([
                    'business_id' => $business->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'current_cycle_start' => Carbon::now(),
                    'current_cycle_end' => Carbon::now()->addYear(),
                    'ends_at' => null, // Auto-renewing
                    'meta' => [
                        'price' => $plan->price,
                        'currency' => $plan->currency,
                        'billing_cycle' => 'yearly',
                    ]
                ]);
                $this->command->info("✅ Subscription created for {$b['name']} ({$plan->name} Plan)");
            }
        }

        $this->command->info('✅ Test User Created: test@vedantbilling.com / password');
        $this->command->info('✅ Business Created: Acme Corp');
    }
}
