<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessFeatureOverride;
use App\Models\Feature;
use App\Models\PlanFeature;
use Illuminate\Support\Facades\Cache;

class FeatureService
{
    /**
     * Check if a business has access to a feature.
     */
    public function hasFeature(Business $business, string $featureSlug): bool
    {
        $limit = $this->getFeatureLimit($business, $featureSlug);

        // If limit is 0, they don't have it.
        // If limit is > 0 or -1 (unlimited), they have it.
        return $limit !== 0;
    }

    /**
     * Get the specific limit for a feature for a business.
     * Returns:
     *  0 = No access
     * -1 = Unlimited
     * >0 = Specific limit
     */
    public function getFeatureLimit(Business $business, string $featureSlug): int
    {
        $cacheKey = "business:{$business->id}:features:{$featureSlug}";

        return Cache::remember($cacheKey, 60 * 60, function () use ($business, $featureSlug) {
            return $this->resolveFeatureLimit($business, $featureSlug);
        });
    }

    /**
     * Resolve the limit by checking overrides, then plan.
     */
    protected function resolveFeatureLimit(Business $business, string $featureSlug): int
    {
        // 0. Global Safety Check (Kill Switch)
        // If the feature feature itself is disabled globally, NO ONE gets it.
        $globalFeature = Feature::where('slug', $featureSlug)->first();

        if (!$globalFeature || !$globalFeature->is_active) {
            return 0;
        }

        // 1. Check for specific Business Override first (Custom Deal)
        $override = BusinessFeatureOverride::where('business_id', $business->id)
            ->whereHas('feature', fn($q) => $q->where('slug', $featureSlug))
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($override) {
            return $override->limit;
        }

        // 2. Check Active Subscription Plan
        // We assume a business has ONE active subscription for the main SaaS product.
        // If you have multiple products, you'd filter by product type here.
        $subscription = $business->subscriptions()
            ->where('status', 'active') // Or trialing
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->with([
                'plan.features' => function ($q) use ($featureSlug) {
                    $q->where('slug', $featureSlug);
                }
            ])
            ->first();

        if (!$subscription || !$subscription->plan) {
            // No active subscription = No features (unless there's a default/fallback plan logic)
            return 0;
        }

        // 3. Check Plan Limits
        $planFeature = $subscription->plan->features->first();

        if ($planFeature) {
            return $planFeature->pivot->limit;
        }

        return 0; // Feature not in plan
    }

    /**
     * Clear feature cache for a business.
     * Call this when plan changes or overrides are updated.
     */
    public function clearCache(Business $business): void
    {
        // Wildcard clearing is tricky with standard Redis driver without tagging.
        // For MVP, we might just accept 1 hour cache or implement tagging if using Redis fully.
        // Or specific clear if we know the slugs.
        // For now, let's assume tags are available or we just let it expire/use specific keys if vital.

        if (Cache::supportsTags()) {
            Cache::tags(["business:{$business->id}"])->flush();
        }
    }
}
