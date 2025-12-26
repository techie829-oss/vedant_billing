<?php

namespace App\Http\Middleware;

use App\Services\FeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureMiddleware
{
    public function __construct(protected FeatureService $featureService) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $featureSlug): Response
    {
        // 1. Ensure we have a Business Context
        $business = $request->attributes->get('business');

        if (!$business) {
            // If checking feature, we MUST have a business context.
            // This middleware should run AFTER 'tenant.context'
            abort(403, 'No business context found.');
        }

        // 2. Check Feature Access
        if (!$this->featureService->hasFeature($business, $featureSlug)) {
            if ($request->expectsJson()) {
                 return response()->json([
                     'message' => 'Your plan does not include this feature.',
                     'feature' => $featureSlug,
                     'upgrade_url' => '/billing/plans' // Frontend can use this
                 ], 403);
            }
            abort(403, 'Feature not available on your plan.');
        }

        return $next($request);
    }
}
