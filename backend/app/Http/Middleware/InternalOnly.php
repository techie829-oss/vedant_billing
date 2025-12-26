<?php

namespace App\Http\Middleware;

use App\Models\InUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalOnly
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
            // If it's a web route, redirect to login
            if (!$request->expectsJson()) {
                return redirect()->route('internal.login');
            }
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $internalUser = InUser::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$internalUser) {
            if (!$request->expectsJson()) {
                abort(403, 'Internal access required.');
            }
            return response()->json(['message' => 'Internal access required.'], 403);
        }

        // Set internal context
        $request->attributes->set('internal_user', $internalUser);

        return $next($request);
    }
}
