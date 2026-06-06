<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    private const ROLE_HIERARCHY = [
        'owner' => 3,
        'admin' => 2,
        'kasir' => 1,
    ];

    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:owner,admin') — user must have owner OR admin role.
     * Role hierarchy: owner > admin > kasir. Higher roles can access lower-role routes.
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userRoleLevel = self::ROLE_HIERARCHY[$user->role] ?? 0;
        $maxRequiredLevel = 0;

        foreach ($allowedRoles as $role) {
            $level = self::ROLE_HIERARCHY[trim($role)] ?? 0;
            $maxRequiredLevel = max($maxRequiredLevel, $level);
        }

        if ($userRoleLevel < $maxRequiredLevel) {
            Log::warning('Unauthorized access attempt blocked', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $allowedRoles,
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Anda tidak memiliki akses ke fitur ini.'
            ], 403);
        }

        return $next($request);
    }
}
