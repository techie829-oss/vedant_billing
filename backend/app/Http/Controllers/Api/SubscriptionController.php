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

        $active = $business->subscriptions()
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere(function ($q) {
                        $q->where('status', 'trialing')
                            ->where('trial_ends_at', '>', now());
                    });
            })
            ->with('plan.features')
            ->latest()
            ->first();

        // Check for pending subscription
        $pending = $business->subscriptions()
            ->where('status', 'pending')
            ->with('plan')
            ->latest()
            ->first();

        if ($active) {
            $usageService = new UsageService();
            $active->usage = $usageService->getUsage($business);
        }

        return response()->json([
            'active' => $active,
            'pending' => $pending
        ]);
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
        Gate::authorize('update', $business);

        // Cancel any other pending subscriptions for cleanliness
        $business->subscriptions()->where('status', 'pending')->forceDelete();

        $plan = Plan::findOrFail($request->plan_id);

        // Create new PENDING subscription
        $subscription = Subscription::create([
            'business_id' => $businessId,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'current_cycle_start' => null,
            'current_cycle_end' => null,
            'meta' => []
        ]);

        return response()->json($subscription, 201);
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
        $businessId = $request->header('X-Business-ID');

        if (!$plan->razorpay_plan_id) {
            return response()->json(['message' => 'This plan is not configured for recurring payment'], 400);
        }

        // Find existing pending or create new
        $subscription = Subscription::where('business_id', $businessId)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$subscription) {
            $subscription = Subscription::create([
                'business_id' => $businessId,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'meta' => ['type' => 'recurring']
            ]);
        } else {
            // Update meta type just in case
            $subscription->update(['meta' => array_merge($subscription->meta ?? [], ['type' => 'recurring'])]);
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
                    'business_id' => $businessId,
                    'internal_subscription_id' => $subscription->id
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();

            // Update meta with razorpay id
            $subscription->update([
                'meta' => array_merge($subscription->meta ?? [], [
                    'razorpay_id' => $data['id'],
                    'provider_status' => $data['status']
                ])
            ]);

            return response()->json(array_merge($data, [
                'key_id' => $keyId,
                'internal_subscription_id' => $subscription->id
            ]));
        }

        // Keep pending subscription, just log error
        Log::error('Razorpay Subscription Failed', ['response' => $response->body()]);
        return response()->json(['message' => 'Failed to create subscription', 'details' => $response->json()], 500);
    }

    /**
     * Create Razorpay Order (One-Time)
     */
    private function createOrder(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);
        $businessId = $request->header('X-Business-ID');

        // Find existing pending or create new
        $subscription = Subscription::where('business_id', $businessId)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$subscription) {
            $subscription = Subscription::create([
                'business_id' => $businessId,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'meta' => ['type' => 'one_time']
            ]);
        } else {
            $subscription->update(['meta' => array_merge($subscription->meta ?? [], ['type' => 'one_time'])]);
        }

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
                    'business_id' => $businessId,
                    'type' => 'one_time',
                    'internal_subscription_id' => $subscription->id
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();

            // Update meta with razorpay order id
            $subscription->update([
                'meta' => array_merge($subscription->meta ?? [], ['razorpay_order_id' => $data['id']])
            ]);

            return response()->json(array_merge($data, [
                'key_id' => $keyId,
                'internal_subscription_id' => $subscription->id
            ]));
        }

        // Keep pending
        Log::error('Razorpay Order Failed', ['response' => $response->body()]);

        return response()->json([
            'message' => 'Failed to create order',
            'details' => $response->json()
        ], 500);
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

            // Find the pending subscription
            // If internal_subscription_id is unknown, we could search by meta->razorpay_order_id
            // But we can require internal_subscription_id or search intelligently

            $query = Subscription::where('business_id', $businessId)->where('status', 'pending');

            if ($isRecurring) {
                // For recurring, we might store subscription_id in meta or just match latest pending
                // or match by plan_id
                // Best is to search by razorpay_id stored in meta
                // But JSON search in MySQL/Postgres varies. 
                // Simple fallback: Fetch all pending and check meta in PHP or just assume the latest pending subscription for this plan
                $subscription = $query->where('plan_id', $request->plan_id)->latest()->first();
            } else {
                // For One Time, check razorpay_order_id in meta
                $orderId = $request->razorpay_order_id;
                $subscription = $query->get()->first(function ($sub) use ($orderId) {
                    return ($sub->meta['razorpay_order_id'] ?? '') === $orderId;
                });
            }

            if (!$subscription) {
                // Fallback: Create new if not found (should not happen in new flow, but good for safety)
                $plan = Plan::findOrFail($request->plan_id);
                $subscription = Subscription::create([
                    'business_id' => $business->id,
                    'plan_id' => $plan->id,
                    'status' => 'pending',
                    'meta' => []
                ]);
            }

            // Deactivate old active subscriptions
            $business->subscriptions()->where('status', 'active')->update(['status' => 'canceled', 'canceled_at' => now()]);

            $plan = $subscription->plan; // Reload plan relationship

            $subscription->update([
                'status' => 'active',
                'current_cycle_start' => now(),
                'current_cycle_end' => $plan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
                'meta' => array_merge($subscription->meta ?? [], [
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_id' => $isRecurring ? $request->razorpay_subscription_id : ($request->razorpay_order_id ?? null),
                    'type' => $isRecurring ? 'recurring' : 'one_time',
                    'auto_renewal' => $isRecurring
                ])
            ]);

            return response()->json(['message' => 'Payment verified. Plan Activated.', 'subscription' => $subscription]);
        }

        return response()->json(['message' => 'Invalid signature'], 400);
    }
}
