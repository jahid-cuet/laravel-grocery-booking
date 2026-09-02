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
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Load role relationship if not loaded
        if (! $user->relationLoaded('role')) {
            $user->load('role');
        }

        $userRoleSlug = $user->role?->slug;

        // Flatten comma-separated roles (e.g. 'role:user,admin' passes as one string)
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        if (! $userRoleSlug || ! in_array($userRoleSlug, $allowedRoles, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. You do not have permission to access this resource.',
                'required_roles' => $allowedRoles,
                'current_role' => $userRoleSlug,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
