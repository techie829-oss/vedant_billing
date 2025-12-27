<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\UsageService;

use Illuminate\Support\Facades\Gate;

class SubscriptionController extends Controller
{
    /**
     * Get the current active subscription for the business.
     */
    public function index(Request $request)
    {
        $businessId = $request->header('X-Business-ID');
        if (!$businessId) {
            return response()->json(['message' => 'Business ID required'], 400);
        }

        $business = Business::findOrFail($businessId);

        // Limit Billing Access to Admins/Owners
        Gate::authorize('update', $business);

        $subscription = $business->subscriptions()
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere(function ($q) {
                        $q->where('status', 'trialing') // Assuming trialing status exists or logic
                            ->where('trial_ends_at', '>', now());
                    });
            })
            ->with('plan.features')
            ->latest()
            ->first();

        Log::info('Subscription retrieved', ['id' => $subscription ? $subscription->id : 'none']);

        if ($subscription) {
            $usageService = new UsageService();
            $subscription->usage = $usageService->getUsage($business);
        }

        return response()->json($subscription);
    }

    /**
     * Subscribe to a plan (Upgrade/Downgrade).
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $businessId = $request->header('X-Business-ID');
        if (!$businessId) {
            return response()->json(['message' => 'Business ID required'], 400);
        }

        $business = Business::findOrFail($businessId);

        // Security Check
        Gate::authorize('update', $business);

        // Deactivate current active subscriptions
        $business->subscriptions()->where('status', 'active')->update(['status' => 'canceled', 'canceled_at' => now()]);

        // Create new subscription
        $plan = Plan::findOrFail($request->plan_id);

        $subscription = Subscription::create([
            'business_id' => $businessId,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_cycle_start' => now(),
            'current_cycle_end' => $plan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);

        return response()->json($subscription->load('plan'), 201);
    }
}
