<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $webhookSecret = config('services.razorpay.webhook_secret', env('RAZORPAY_WEBHOOK_SECRET'));
        $signature = $request->header('X-Razorpay-Signature');

        if (!$this->verifySignature($request->getContent(), $signature, $webhookSecret)) {
            Log::warning('Razorpay Webhook: Invalid Signature');
            return response()->json(['message' => 'Invalid Signature'], 400);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? null;

        Log::info('Razorpay Webhook Received', ['event' => $event]);

        switch ($event) {
            case 'subscription.charged':
                $this->handleSubscriptionCharged($payload);
                break;
            // Add other cases (order.paid, subscription.cancelled) as needed
        }

        return response()->json(['status' => 'ok']);
    }

    private function verifySignature($payload, $signature, $secret)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    private function handleSubscriptionCharged($payload)
    {
        try {
            $subscriptionId = $payload['payload']['subscription']['entity']['id'];
            $paymentId = $payload['payload']['payment']['entity']['id'];

            // Find our local subscription
            // We store razorpay_subscription_id in meta->razorpay_id or meta->razorpay_subscription_id
            // JSON searching in MySQL/Postgres depends on version, using whereJsonContains or strict string search on meta column if it's text.
            // Assuming 'meta' is a JSON castable column.

            $subscription = Subscription::whereJsonContains('meta->razorpay_subscription_id', $subscriptionId)
                ->orWhereJsonContains('meta->razorpay_id', $subscriptionId)
                ->first();

            if ($subscription) {
                // Extend the subscription
                // Razorpay gives new end_at, or we just add interval.
                // best is to trust Razorpay's current_end
                $endedAt = $payload['payload']['subscription']['entity']['end_at'] ?? null; // Timestamp

                if ($endedAt) {
                    $subscription->update([
                        'current_cycle_end' => \Carbon\Carbon::createFromTimestamp($endedAt),
                        'status' => 'active' // Ensure it's active
                    ]);

                    Log::info("Subscription {$subscription->id} extended via Webhook.");
                }
            } else {
                Log::warning("Webhook: Subscription not found for ID: {$subscriptionId}");
            }

        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
        }
    }
}
