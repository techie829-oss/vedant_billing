<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired subscriptions and downgrade to Free tier';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired subscriptions...');

        // 1. Find the Free Plan
        $freePlan = \App\Models\Plan::where('slug', 'free')->first();
        if (!$freePlan) {
            $this->error('Free plan not found. Cannot downgrade.');
            return;
        }

        // 2. Find active subscriptions that have passed their end date
        $expiredSubs = \App\Models\Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->get();

        if ($expiredSubs->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return;
        }

        $count = 0;
        foreach ($expiredSubs as $sub) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($sub, $freePlan) {
                // A. Mark current as expired
                $sub->update(['status' => 'expired']);

                // B. Create new Free subscription
                // Check if they already have a future free sub (unlikely but safe)
                $exists = \App\Models\Subscription::where('business_id', $sub->business_id)
                    ->where('plan_id', $freePlan->id)
                    ->where('status', 'active')
                    ->exists();

                if (!$exists) {
                    \App\Models\Subscription::create([
                        'business_id' => $sub->business_id,
                        'plan_id' => $freePlan->id,
                        'status' => 'active',
                        'current_cycle_start' => now(),
                        // Free plan might be monthly cycle or indefinite. Let's say monthly for reporting.
                        'current_cycle_end' => now()->addMonth(),
                    ]);
                }
            });

            $this->info("Downgraded Business ID: {$sub->business_id} to Free Plan.");
            $count++;
        }

        $this->info("Processed {$count} expired subscriptions.");
    }
}
