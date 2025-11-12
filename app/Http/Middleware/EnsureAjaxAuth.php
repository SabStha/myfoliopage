<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAjaxAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to AJAX requests
        if ($request->expectsJson() || 
            $request->ajax() || 
            $request->wantsJson() ||
            $request->header('X-Requested-With') === 'XMLHttpRequest') {
            
            // If not authenticated, return JSON instead of redirecting
            if (!Auth::check()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Your session has expired. Please log in again.',
                    'redirect' => route('login')
                ], 401);
            }
        }

        return $next($request);
    }
}



