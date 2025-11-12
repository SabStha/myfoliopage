<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
        $middleware->trustProxies(at: '*');
        // Use custom Authenticate middleware - override the default auth middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // No scheduled tasks needed for simple LinkedIn integration
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions for AJAX requests
        // This catches ALL AuthenticationException instances, including from default middleware
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            // Log for debugging
            \Log::info('AuthenticationException caught in exception handler', [
                'isAjax' => $request->header('X-Requested-With') === 'XMLHttpRequest',
                'header' => $request->header('X-Requested-With'),
                'accept' => $request->header('Accept'),
                'url' => $request->url(),
                'user' => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null,
                'session_id' => $request->hasSession() ? $request->session()->getId() : 'no session',
            ]);
            
            // Check if this is an AJAX request - be very specific
            $isAjax = $request->header('X-Requested-With') === 'XMLHttpRequest';
            
            if ($isAjax) {
                \Log::info('Returning JSON response for AJAX request');
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Your session has expired. Please log in again.',
                    'redirect' => route('login')
                ], 401)->header('Content-Type', 'application/json');
            }
            
            // Return null to let Laravel handle the redirect normally
            return null;
        });
    })->create();
