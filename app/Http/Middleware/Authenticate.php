<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // Check if user is authenticated
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);
                return $next($request);
            }
        }

        // Check if this is an AJAX request - check multiple ways
        $isAjax = $request->expectsJson() || 
                  $request->ajax() || 
                  $request->wantsJson() ||
                  $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                  str_contains($request->header('Accept', ''), 'application/json') ||
                  $request->header('Accept') === 'text/html,application/json';

        // If not authenticated and it's an AJAX request, return JSON directly
        if ($isAjax) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Your session has expired. Please log in again.',
                'redirect' => route('login')
            ], 401)->header('Content-Type', 'application/json');
        }

        // For regular requests, use parent's unauthenticated method to trigger redirect
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectTo($request)
        );
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return route('login');
    }
}

