<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Business;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Stats
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $totalBusinesses = Business::count();

        // Revenue Calculation (Simple sum of plan prices for recurring subs)
        $monthlyRevenue = Subscription::where('subscriptions.status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        // List
        $subscriptions = Subscription::with(['business', 'plan'])
            ->latest()
            ->paginate(20);

        return view('internal.subscriptions.index', compact('subscriptions', 'activeSubscriptions', 'totalBusinesses', 'monthlyRevenue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $businessId = $request->query('business_id');
        $business = Business::findOrFail($businessId);
        $plans = \App\Models\Plan::where('status', 'active')->orderBy('price')->get();

        return view('internal.subscriptions.create', compact('business', 'plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $business = Business::findOrFail($request->business_id);
        $plan = \App\Models\Plan::findOrFail($request->plan_id);

        // Deactivate current active subscriptions
        Subscription::where('business_id', $business->id)
            ->where('status', 'active')
            ->update(['status' => 'inactive', 'ends_at' => now()]);

        // Create new subscription
        Subscription::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_cycle_start' => now(),
            'current_cycle_end' => $plan->interval === 'monthly' ? now()->addMonth() : now()->addYear(),
        ]);

        return redirect()->route('internal.tenants.show', $business->id)
            ->with('success', 'Subscription updated successfully.');
    }
}
