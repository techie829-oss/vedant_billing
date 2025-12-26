<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Check if we are in Internal Context
        $internalUser = $request->attributes->get('internal_user');

        if ($internalUser) {
            // Check against InternalRole enum
            // We assume internal roles are passed as-is or we map them.
            // For simplicity, if the route expects 'super_admin', we check that.

            // If the route doesn't specify roles, or if the user has one of the required roles
            // For internal users, 'access_level' column corresponds to InternalRole
            if (in_array($internalUser->access_level, $roles)) {
                return $next($request);
            }

            // Special case: Super Admin usually can access everything internal
            if ($internalUser->access_level === \App\Enums\InternalRole::SUPER_ADMIN->value) {
                return $next($request);
            }
        }

        // 2. Check if we are in Tenant Context
        $membership = $request->attributes->get('membership');

        if ($membership) {
            // Check against Role enum
            if (in_array($membership->role, $roles)) {
                return $next($request);
            }
        }

        // Fallback or Fail
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden. Required role: ' . implode(', ', $roles)], 403);
        }
        abort(403, 'Unauthorized action.');
    }
}
