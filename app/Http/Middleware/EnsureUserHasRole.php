<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->guest(route('login'));
        }

        // Load role relationship if not already eager-loaded
        if (! $user->relationLoaded('role')) {
            $user->load('role');
        }

        // Validate user role directly without nested loops
        if (! empty($roles) && ! $user->hasRole($roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Forbidden. You do not have permission to access this resource.',
                    'required_roles' => $roles,
                    'current_role' => $user->role?->slug,
                ], Response::HTTP_FORBIDDEN);
            }

            abort(403, 'Forbidden. You do not have permission to access this page.');
        }

        return $next($request);
    }
}
