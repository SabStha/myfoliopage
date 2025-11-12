<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ trans('app.auth.login', [], app()->getLocale()) ?: 'Login' }} - {{ config('app.name', 'Portfolio') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            @keyframes gradient-shift {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }
            .animate-float {
                animation: float 6s ease-in-out infinite;
            }
            .gradient-bg {
                background: linear-gradient(-45deg, #e0e7ff, #f3e8ff, #fef3c7, #fce7f3);
                background-size: 400% 400%;
                animation: gradient-shift 15s ease infinite;
            }
            
            /* Ensure translation button doesn't overlap content */
            #translation-button-root {
                pointer-events: none;
                position: fixed;
                top: 1rem;
                right: 1rem;
                z-index: 30;
            }
            
            #translation-button-root button {
                pointer-events: auto;
                max-width: calc(100vw - 2rem);
            }
            
            /* Ensure modals and overlays are above the button */
            [class*="modal"], [class*="overlay"], [id*="modal"] {
                z-index: 50 !important;
            }
            
            /* Ensure dropdowns and popovers are above the button */
            [class*="dropdown"], [class*="popover"] {
                z-index: 40 !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex items-center justify-center gradient-bg py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <!-- Logo/Brand Section -->
                <div class="text-center mb-8 animate-float">
                    <a href="/" class="inline-block">
                        <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-[#ffb400] to-[#ff9500] rounded-2xl shadow-lg flex items-center justify-center transform hover:scale-110 transition-transform duration-300">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ config('app.name', 'Portfolio') }}</h1>
                    </a>
                </div>

                <!-- Card -->
                <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl overflow-hidden border border-white/20">
                    <div class="px-8 py-10">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Back to Home Link -->
                <div class="mt-6 text-center">
                    <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#ffb400] transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ trans('app.auth.back_to_home', [], app()->getLocale()) ?: 'Back to Home' }}
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Translation Button --}}
        <div id="translation-button-root"></div>
        
        <script>
            // Suppress browser extension errors (they don't affect functionality)
            window.addEventListener('error', function(e) {
                // Ignore errors from browser extensions
                if (e.filename && (
                    e.filename.includes('chrome-extension://') ||
                    e.filename.includes('content_script.js') ||
                    e.filename.includes('extensionState.js') ||
                    e.filename.includes('utils.js')
                )) {
                    e.preventDefault();
                    return true;
                }
            }, true);
            
            // Suppress unhandled promise rejections from extensions
            window.addEventListener('unhandledrejection', function(e) {
                const error = e.reason;
                if (error && error.stack && (
                    error.stack.includes('chrome-extension://') ||
                    error.stack.includes('content_script.js')
                )) {
                    e.preventDefault();
                    return true;
                }
            });
        </script>
    </body>
</html>
