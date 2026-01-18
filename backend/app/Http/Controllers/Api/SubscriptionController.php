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

    /**
     * Initiate Payment (Dispatch to Order or Subscription)
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'type' => 'required|in:subscription,one_time'
        ]);

        if ($request->type === 'subscription') {
            return $this->createSubscription($request);
        } else {
            return $this->createOrder($request);
        }
    }

    /**
     * Create Razorpay Subscription (Recurring)
     */
    private function createSubscription(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);

        if (!$plan->razorpay_plan_id) {
            return response()->json(['message' => 'This plan is not configured for recurring payment'], 400);
        }

        $keyId = config('services.razorpay.key_id', env('RAZORPAY_KEY_ID'));
        $keySecret = config('services.razorpay.key_secret', env('RAZORPAY_KEY_SECRET'));

        $apiBase = 'https://api.razorpay.com/v1/subscriptions';

        $response = \Illuminate\Support\Facades\Http::withBasicAuth($keyId, $keySecret)
            ->post($apiBase, [
                'plan_id' => $plan->razorpay_plan_id,
                'total_count' => 120,
                'quantity' => 1,
                'customer_notify' => 1,
                'notes' => [
                    'internal_plan_id' => $plan->id,
                    'business_id' => $request->header('X-Business-ID')
                ]
            ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        Log::error('Razorpay Subscription Failed', ['response' => $response->body()]);
        return response()->json(['message' => 'Failed to create subscription', 'details' => $response->json()], 500);
    }

    /**
     * Create Razorpay Order (One-Time)
     */
    private function createOrder(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);

        $keyId = config('services.razorpay.key_id', env('RAZORPAY_KEY_ID'));
        $keySecret = config('services.razorpay.key_secret', env('RAZORPAY_KEY_SECRET'));

        $apiBase = 'https://api.razorpay.com/v1/orders';
        $amount = $plan->price * 100; // Paise

        $response = \Illuminate\Support\Facades\Http::withBasicAuth($keyId, $keySecret)
            ->post($apiBase, [
                'amount' => $amount,
                'currency' => 'INR',
                'receipt' => 'ord_' . time(),
                'notes' => [
                    'plan_id' => $plan->id,
                    'business_id' => $request->header('X-Business-ID'),
                    'type' => 'one_time'
                ]
            ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['message' => 'Failed to create order'], 500);
    }

    /**
     * Verify Payment (Handles both Order and Subscription)
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'plan_id' => 'required|exists:plans,id'
        ]);

        $keySecret = config('services.razorpay.key_secret', env('RAZORPAY_KEY_SECRET'));
        $isValid = false;
        $isRecurring = false;

        // Determine verification strategy
        if ($request->has('razorpay_subscription_id')) {
            // Subscription Verification
            $request->validate(['razorpay_subscription_id' => 'required|string']);
            $expectedSignature = hash_hmac('sha256', $request->razorpay_payment_id . '|' . $request->razorpay_subscription_id, $keySecret);
            if ($expectedSignature === $request->razorpay_signature) {
                $isValid = true;
                $isRecurring = true;
            }
        } elseif ($request->has('razorpay_order_id')) {
            // One-Time Order Verification
            $request->validate(['razorpay_order_id' => 'required|string']);
            $expectedSignature = hash_hmac('sha256', $request->razorpay_order_id . "|" . $request->razorpay_payment_id, $keySecret);
            if ($expectedSignature === $request->razorpay_signature) {
                $isValid = true;
                $isRecurring = false;
            }
        }

        if ($isValid) {
            $businessId = $request->header('X-Business-ID');
            $business = Business::findOrFail($businessId);
            Gate::authorize('update', $business);

            // Deactivate old active subscriptions
            $business->subscriptions()->where('status', 'active')->update(['status' => 'canceled', 'canceled_at' => now()]);

            $plan = Plan::findOrFail($request->plan_id);

            $subscription = Subscription::create([
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'current_cycle_start' => now(),
                'current_cycle_end' => $plan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
                'meta' => [
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_id' => $isRecurring ? $request->razorpay_subscription_id : $request->razorpay_order_id,
                    'type' => $isRecurring ? 'recurring' : 'one_time',
                    'auto_renewal' => $isRecurring
                ]
            ]);

            return response()->json(['message' => 'Payment verified. Plan Activated.', 'subscription' => $subscription]);
        }

        return response()->json(['message' => 'Invalid signature'], 400);
    }
}
