<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Subscription;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // 1. Total Tenants
        $totalTenants = Business::count();
        $newTenantsLast30Days = Business::where('created_at', '>=', now()->subDays(30))->count();

        // 2. Active Subscriptions
        $activeSubscriptions = Subscription::where('status', 'active')->count();

        // 3. Revenue (MRR Estimate)
        // Normalize yearly plans to monthly (price / 12)
        $mrr = Subscription::where('subscriptions.status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum(\Illuminate\Support\Facades\DB::raw("CASE WHEN plans.interval = 'yearly' THEN plans.price / 12 ELSE plans.price END"));

        // 4. Recent Businesses
        $recentBusinesses = Business::with(['subscriptions.plan'])
            ->latest()
            ->take(5)
            ->get();

        return view('internal.dashboard', compact(
            'totalTenants',
            'newTenantsLast30Days',
            'activeSubscriptions',
            'mrr',
            'recentBusinesses'
        ));
    }
}
