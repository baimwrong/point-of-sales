<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 *
 * Guards routes by checking:
 *  1. The user is authenticated.
 *  2. The user's account is active.
 *  3. The user's role matches one of the allowed roles.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     * @param  string  ...$roles  One or more allowed role names.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account has been deactivated.'], 403);
            }
            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated.');
        }

        if (!empty($roles)) {
            $userRole = $user->role?->name;
            if (!$userRole || !in_array($userRole, $roles)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthorized action.'], 403);
                }
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}
