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
        // Use custom Authenticate middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
        // Register custom AJAX auth middleware
        $middleware->alias([
            'ajax.auth' => \App\Http\Middleware\EnsureAjaxAuth::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // No scheduled tasks needed for simple LinkedIn integration
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle authentication exceptions for AJAX requests
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            // Check multiple ways if this is an AJAX request
            $isAjax = $request->expectsJson() || 
                     $request->ajax() || 
                     $request->wantsJson() ||
                     $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                     str_contains($request->header('Accept', ''), 'application/json') ||
                     $request->header('Accept') === 'text/html,application/json';
            
            if ($isAjax) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Your session has expired. Please log in again.',
                    'redirect' => route('login')
                ], 401)->header('Content-Type', 'application/json');
            }
        });
    })->create();
