@extends('layouts.app')
@section('title', 'Create Your Portfolio')
@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    {{-- Hero Section --}}
    <section class="relative min-h-screen flex items-center justify-center px-4 py-20">
        <div class="max-w-6xl mx-auto text-center">
            <h1 class="text-5xl md:text-7xl font-bold text-gray-900 mb-6">
                Create Your Professional Portfolio
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-3xl mx-auto">
                Showcase your projects, certificates, and achievements in a beautiful, customizable portfolio. 
                Free to use, easy to manage.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="px-8 py-4 bg-blue-600 text-white rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors">
                    Get Started Free
                </a>
                <a href="{{ route('login') }}" class="px-8 py-4 bg-white text-blue-600 border-2 border-blue-600 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colors">
                    Sign In
                </a>
            </div>
        </div>
    </section>

    {{-- Template Preview Section - Show Actual Portfolio Template --}}
    @if($sampleUser && isset($heroSection))
    <section class="py-20 px-4 bg-white border-t-4 border-blue-600">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    See What Your Portfolio Will Look Like
                </h2>
                <p class="text-xl text-gray-600 mb-2">
                    Here's a live example from <strong>{{ $sampleUser->name }}</strong>
                </p>
                <a href="{{ url('/' . ($sampleUser->username ?? $sampleUser->slug ?? $sampleUser->id)) }}" 
                   class="inline-block text-blue-600 hover:text-blue-800 font-semibold text-lg underline"
                   target="_blank">
                    View Full Portfolio →
                </a>
            </div>
            
            {{-- Actual Portfolio Template Preview --}}
            <div class="border-4 border-gray-300 rounded-lg overflow-hidden shadow-2xl" style="max-height: 80vh; overflow-y: auto;">
                {{-- Include the portfolio preview partial --}}
                @include('partials.portfolio-preview', [
                    'user' => $sampleUser,
                    'profile' => $profile ?? null,
                    'heroSection' => $heroSection ?? null,
                    'engagementSection' => $engagementSection ?? null,
                    'engagementVideo' => $engagementVideo ?? null,
                    'heroProfileImages' => $heroProfileImages ?? [],
                    'profileImages' => $profileImages ?? [],
                    'finalProfileImages' => $finalProfileImages ?? [],
                    'categories' => $categories ?? collect(),
                    'services' => $services ?? [],
                    'homePageSections' => $homePageSections ?? [],
                    'progressItems' => $progressItems ?? [],
                    'certificatesData' => $certificatesData ?? [],
                    'coursesData' => $coursesData ?? [],
                    'roomsData' => $roomsData ?? [],
                    'badgesData' => $badgesData ?? [],
                    'gamesData' => $gamesData ?? [],
                    'simulationsData' => $simulationsData ?? [],
                    'programsData' => $programsData ?? [],
                    'blogs' => $blogs ?? collect()
                ])
            </div>
        </div>
    </section>
    @endif

    {{-- Features Section --}}
    <section class="py-20 px-4 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-4xl font-bold text-center text-gray-900 mb-12">
                Everything You Need
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="text-4xl mb-4">🎨</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Beautiful Design</h3>
                    <p class="text-gray-600">Modern, responsive templates that look great on any device</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl mb-4">⚡</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Easy to Manage</h3>
                    <p class="text-gray-600">Simple admin panel to add and organize your content</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl mb-4">🌐</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Your Own URL</h3>
                    <p class="text-gray-600">Share your portfolio with a custom URL: yourname.portfolio.com</p>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Popup Modal (appears after 5 seconds) --}}
<div id="portfolio-popup" 
     x-data="{ show: false }"
     x-init="setTimeout(() => { show = true }, 5000)"
     x-show="show"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
     style="display: none;">
    <div class="bg-white rounded-lg p-8 max-w-md mx-4 shadow-2xl" @click.away="show = false">
        <h2 class="text-2xl font-bold text-gray-900 mb-4 text-center">
            Are you ready to make your portfolio?
        </h2>
        <p class="text-gray-600 mb-6 text-center">
            Join thousands of professionals showcasing their work
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="{{ route('register') }}" 
               class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold text-center hover:bg-blue-700 transition-colors">
                Register
            </a>
            <a href="{{ route('login') }}" 
               class="flex-1 px-6 py-3 bg-white text-blue-600 border-2 border-blue-600 rounded-lg font-semibold text-center hover:bg-blue-50 transition-colors">
                Login
            </a>
        </div>
        <button @click="show = false" 
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endsection

