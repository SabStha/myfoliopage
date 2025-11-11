<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority: URL parameter > session > cookie > default
        $locale = $request->query('lang')
            ?? Session::get('locale') 
            ?? $request->cookie('locale')
            ?? config('app.locale', 'en');

        // Validate locale (only allow en or ja)
        if (!in_array($locale, ['en', 'ja'])) {
            $locale = 'en';
        }

        // Set the application locale
        App::setLocale($locale);
        
        // Store in session for next requests
        Session::put('locale', $locale);

        $response = $next($request);
        
        // Set cookie for client-side access (30 days)
        return $response->cookie('locale', $locale, 60 * 24 * 30);
    }
}

