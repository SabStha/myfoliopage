<aside x-data="{ open: false }" 
      @toggle-sidebar.window="open = !open"
      :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
      class="fixed top-0 left-0 h-full w-[280px] bg-white shadow-lg border-r border-gray-200 flex flex-col items-center py-4 sm:py-8 z-50 transition-transform duration-300 ease-in-out">
    @php
        $user = Auth::user();
        $p = $user ? \App\Models\Profile::where('user_id', $user->id)->first() : null;
    @endphp

    <div class="flex items-center justify-between w-full px-4 mb-4 lg:hidden">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('app.admin.menu') }}</h2>
        <button @click="open = false" class="p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @php
        $profilePhotoUrl = null;
        if ($p && $p->photo_path) {
            if (strpos($p->photo_path, 'images/') === 0) {
                $profilePhotoUrl = asset($p->photo_path);
            } elseif (strpos($p->photo_path, 'storage/') === 0 || strpos($p->photo_path, '/storage/') === 0) {
                $profilePhotoUrl = asset($p->photo_path);
            } elseif (strpos($p->photo_path, 'http') === 0) {
                $profilePhotoUrl = $p->photo_path;
            } else {
                $profilePhotoUrl = asset('storage/' . $p->photo_path);
            }
        } else {
            $profilePhotoUrl = asset('images/profile.jpg');
        }
    @endphp
    <img src="{{ $profilePhotoUrl }}" class="w-20 h-20 sm:w-28 sm:h-28 rounded-full shadow-md object-cover" alt="Profile" />
    <h2 class="mt-2 sm:mt-4 text-base sm:text-xl font-semibold text-gray-900 text-center px-4">{{ $p?->name ?? $user?->name ?? 'Your Name' }}</h2>
    <p class="text-xs sm:text-sm text-gray-500 text-center px-4">{{ $p?->role ?? 'Full-Stack / Security Student' }}</p>
    @if(request()->routeIs('admin.*'))
        <a href="{{ route('admin.profile.edit') }}" class="mt-2 text-xs text-amber-700 hover:underline">{{ __('app.admin.edit_profile') }}</a>
    @endif

    

    @if(!request()->routeIs('admin.*'))
        <div class="mt-6 w-[80%]">
            <h3 class="text-xs font-semibold text-gray-600">Skills</h3>
            <div class="mt-2">
                <p class="text-xs text-gray-500">Laravel</p>
                <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-yellow-400 h-2 rounded-full w-[90%]"></div></div>
            </div>
            <div class="mt-2">
                <p class="text-xs text-gray-500">React</p>
                <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-yellow-400 h-2 rounded-full w-[75%]"></div></div>
            </div>
        </div>
        <a href="/cv.pdf" class="mt-6 px-6 py-2 bg-yellow-400 text-black font-semibold rounded-lg shadow hover:bg-yellow-500">Download CV</a>
    @endif

    <nav class="space-y-1 mt-4 sm:mt-8 w-full px-2 sm:px-4 overflow-y-auto flex-1">
        @if(request()->routeIs('admin.*'))
            @php
                $isDashboard = request()->routeIs('admin.dashboard');
                $dashClasses = $isDashboard ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 {{ $dashClasses }} text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0">
                    <path d="M12 3.172 2.293 12.88a1 1 0 0 0 1.414 1.415L5 13.002V20a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-4h2v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-6l1.293 1.293a1 1 0 0 0 1.414-1.415L12 3.172z"/>
                </svg>
                <span class="truncate">{{ __('app.admin.dashboard_title') }}</span>
            </a>
            @php
                $isHero = request()->routeIs('admin.hero.*');
                $heroClasses = $isHero ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            <a href="{{ route('admin.hero.edit') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 {{ $heroClasses }} text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="truncate">{{ __('app.admin.hero_section') }}</span>
            </a>
            @php
                $isEngagement = request()->routeIs('admin.engagement.*');
                $engagementClasses = $isEngagement ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            <a href="{{ route('admin.engagement.edit') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 {{ $engagementClasses }} text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span class="truncate">{{ __('app.admin.engagement_section') }}</span>
            </a>
            @php
                $isProgress = request()->routeIs('admin.ongoing-progress.*');
                $progressClasses = $isProgress ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            <a href="{{ route('admin.ongoing-progress.index') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 {{ $progressClasses }} text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="truncate">{{ __('app.admin.ongoing_progress') }}</span>
            </a>
            @php
                $isHomePageSections = request()->routeIs('admin.home-page-sections.*');
                $homePageSectionsClasses = $isHomePageSections ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            <a href="{{ route('admin.home-page-sections.index') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 {{ $homePageSectionsClasses }} text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                <span class="truncate">{{ __('app.admin.home_page_sections') }}</span>
            </a>
            @php
                $isBlogs = request()->routeIs('admin.blogs.*');
                $blogsClasses = $isBlogs ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            <a href="{{ route('admin.blogs.index') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 {{ $blogsClasses }} text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <span class="truncate">{{ __('app.admin.blog_posts') }}</span>
            </a>
            @php
                $isLinkedIn = request()->routeIs('admin.linkedin.*');
                $linkedInClasses = $isLinkedIn ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            @php
                $isTestimonials = request()->routeIs('admin.testimonials.*');
                $testimonialsClasses = $isTestimonials ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            <a href="{{ route('admin.testimonials.index') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 {{ $testimonialsClasses }} text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <span class="truncate">{{ __('app.admin.testimonials') }}</span>
            </a>
        @endif
        @php
            // Get only NavItems belonging to the authenticated user
            $userId = Auth::id();
            $items = \App\Models\NavItem::where('user_id', $userId)
                ->orderBy('position')
                ->get()
                ->filter(function($item) {
                    $label = $item->getTranslated('label', 'en') ?: '';
                    $labelLower = strtolower($label);
                    return !in_array($labelLower, ['books', 'udemy', 'tryhackme']);
                });
        @endphp
        @foreach($items as $item)
            @php
                // Determine link URL - prefer url, then route, then NavLinks
                $href = $item->url ?: ($item->route ? route($item->route) : route('admin.nav.links.index', $item));
                
                // Determine active state based on actual href destination
                $isActive = $item->active_pattern
                    ? request()->routeIs($item->active_pattern)
                    : ($item->route ? request()->routeIs($item->route) : request()->routeIs('admin.nav.links.*') && request()->route('nav')->id === $item->id);
                $classes = $isActive ? 'bg-amber-50 text-amber-900' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-900';
            @endphp
            <a href="{{ $href }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 {{ $classes }} text-sm sm:text-base">
                @if($item->icon_svg)
                    <div class="flex-shrink-0">{!! str_replace('class="h-5 w-5"', 'class="h-4 w-4 sm:h-5 sm:w-5"', $item->icon_svg) !!}</div>
                @else
                    <span class="h-4 w-4 sm:h-5 sm:w-5 rounded bg-slate-200 block flex-shrink-0"></span>
                @endif
                <span class="truncate">{{ $item->getTranslated('label') ?: 'Untitled' }}</span>
            </a>
        @endforeach

        <div class="pt-4 border-t border-gray-200 mt-auto space-y-1">
            @php
                $user = Auth::user();
                $portfolioUrl = $user && $user->username ? route('portfolio.show', $user->username) : route('landing');
            @endphp
            <a href="{{ $portfolioUrl }}" target="_blank" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span class="truncate">{{ __('app.admin.view_public_homepage') }}</span>
            </a>
            <a href="{{ route('admin.nav.index') }}" class="flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 text-slate-600 hover:text-slate-900 hover:bg-amber-50 text-sm sm:text-base">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="truncate">{{ __('app.admin.customize_sidebar') }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 text-sm sm:text-base transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="truncate">{{ __('app.admin.logout') ?: 'Logout' }}</span>
                </button>
            </form>
            <button 
                type="button" 
                @click="$dispatch('open-delete-account-modal')"
                class="w-full flex items-center gap-2 sm:gap-3 rounded-xl px-2 sm:px-3 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 text-sm sm:text-base transition-colors mt-1"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span class="truncate">{{ __('app.admin.delete_account') ?: 'Delete Account' }}</span>
            </button>
        </div>
    </nav>
</aside>

{{-- Delete Account Modal --}}
<div 
    x-data="{ open: false }"
    @open-delete-account-modal.window="open = true"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    style="display: none;"
    @click.away="open = false"
>
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6" @click.stop>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.admin.delete_account') ?: 'Delete Account' }}</h3>
        <p class="text-sm text-gray-600 mb-6">
            {{ __('app.admin.delete_account_warning') ?: 'Are you sure you want to delete your account? This action cannot be undone. All your data will be permanently deleted.' }}
        </p>
        <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
            @csrf
            @method('DELETE')
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('app.admin.confirm_password') ?: 'Confirm Password' }}
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="{{ __('app.admin.enter_password_to_confirm') ?: 'Enter your password to confirm' }}"
                />
                @error('password', 'userDeletion')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button 
                    type="button" 
                    @click="open = false"
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                >
                    {{ __('app.common.cancel') ?: 'Cancel' }}
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    {{ __('app.admin.delete_account') ?: 'Delete Account' }}
                </button>
            </div>
        </form>
    </div>
</div>


