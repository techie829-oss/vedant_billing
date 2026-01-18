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
}
