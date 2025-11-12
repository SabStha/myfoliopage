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
    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        // Check if this is an AJAX request - be very specific
        $isAjax = $request->header('X-Requested-With') === 'XMLHttpRequest';
        
        if ($isAjax) {
            // Return JSON response directly instead of throwing exception
            abort(response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Your session has expired. Please log in again.',
                'redirect' => route('login')
            ], 401));
        }

        // For regular requests, throw exception to trigger redirect
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirectTo($request)
        );
    }
    
    public function handle($request, Closure $next, ...$guards)
    {
        // Use parent's handle method which will call our unauthenticated method
        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return route('login');
    }
}

