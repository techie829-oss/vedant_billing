<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $businessId = $request->header('X-Business-ID');

        if (!$businessId) {
            return response()->json(['message' => 'X-Business-ID header is required.'], 400);
        }

        $business = Business::where('id', $businessId)->first();

        if (!$business) {
            return response()->json(['message' => 'Business not found.'], 404);
        }

        // Check if user belongs to this business
        $membership = $user->businesses()
            ->where('business_id', $businessId)
            ->wherePivot('status', 'active')
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access to this business.'], 403);
        }

        // Set context
        // You can register this in a ServiceContainer or request attributes
        $request->attributes->set('business', $business);
        $request->attributes->set('membership', $membership->pivot);

        return $next($request);
    }
}
