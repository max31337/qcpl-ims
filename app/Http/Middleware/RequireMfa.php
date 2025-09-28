<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMfa
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Skip MFA check if user is not authenticated
        if (!$user) {
            return $next($request);
        }
        
        // Skip MFA check if user doesn't have MFA enabled
        if (!$user->mfa_enabled) {
            return $next($request);
        }
        
        // Skip MFA check for MFA verification routes
        if ($request->routeIs('mfa.*')) {
            return $next($request);
        }
        
        // Check if user has completed MFA verification in this session
        if (!session('mfa_verified_at') || session('mfa_verified_at') < now()->subHours(8)) {
            // Store the intended URL
            session(['url.intended' => $request->url()]);
            return redirect()->route('mfa.challenge');
        }
        
        return $next($request);
    }
}