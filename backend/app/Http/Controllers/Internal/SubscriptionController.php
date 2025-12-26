<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions.
     */
    public function index()
    {
        $subscriptions = Subscription::with(['business', 'plan'])
            ->latest()
            ->paginate(20);

        return view('internal.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Show the form for creating a new subscription manually.
     */
    public function create(Request $request)
    {
        // If specific business requested (e.g. from Tenant Show page query param)
        if ($request->has('business_id')) {
            $businesses = Business::where('id', $request->business_id)->get();
        } else {
            // General "Add Subscription" flow - maybe show all or just those without?
            // Let's show all for flexibility, or perhaps limit to 50 latest.
            $businesses = Business::latest()->take(100)->get();
        }
        // For SaaS, usually one active sub per business.
        $plans = Plan::where('status', 'active')->get();

        return view('internal.subscriptions.create', compact('businesses', 'plans'));
    }

    /**
     * Store a newly created subscription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|in:active,trialing',
            'trial_days' => 'nullable|integer|min:0',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        // Calculate dates
        $trialEndsAt = null;
        $startsAt = now();
        $endsAt = null;

        if ($request->status === 'trialing' && $request->trial_days) {
            $trialEndsAt = now()->addDays($request->trial_days);
        }

        // Simple logic: if active, set ends_at based on interval
        if ($request->status === 'active') {
            $endsAt = $plan->interval === 'yearly' ? now()->addYear() : now()->addMonth();

            // Enforce Single Active Subscription Rule
            // Cancel any other currently active subscriptions for this business
            Subscription::where('business_id', $request->business_id)
                ->where('status', 'active')
                ->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'ends_at' => now(),
                ]);
        }

        Subscription::create([
            'business_id' => $request->business_id,
            'plan_id' => $plan->id,
            'status' => $request->status,
            'trial_ends_at' => $trialEndsAt,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return redirect()->route('internal.subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(string $id)
    {
        $subscription = Subscription::findOrFail($id);

        $subscription->update([
            'ends_at' => now(),
            'canceled_at' => now(),
            'status' => 'canceled'
        ]);

        return back()->with('success', 'Subscription canceled.');
    }
}
