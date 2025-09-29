<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Accepts a comma-separated list of allowed roles.
     */
    public function handle(Request $request, Closure $next, string $roles = null): Response
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $allowed = array_map('trim', explode(',', $roles));

        if (!in_array($user->role, $allowed)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
