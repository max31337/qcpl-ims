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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        if (!$user) {
            \Log::error('CheckRole: No authenticated user');
            abort(403, 'Unauthorized.');
        }

        if (empty($roles)) {
            \Log::info('CheckRole: No roles specified, allowing through');
            return $next($request);
        }

        // Handle both single parameter with comma-separated values and multiple parameters
        if (count($roles) === 1 && strpos($roles[0], ',') !== false) {
            $allowed = array_map('trim', explode(',', $roles[0]));
        } else {
            $allowed = $roles;
        }
        
        if (!in_array($user->role, $allowed)) {
            abort(403, 'Unauthorized.');
        }
        return $next($request);
    }
}
