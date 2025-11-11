<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-locale="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Portfolio')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
          html, body { 
            font-family: 'Poppins', sans-serif;
            height: 100%;
          }
          [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="min-h-screen bg-[#f5f6fa] text-[#111]">
        @php($isAdmin = request()->routeIs('admin.*'))
        @if($isAdmin)
            <div class="fixed top-0 left-0 h-2 w-full bg-[#ffb400] z-40"></div>
        @endif
        <div class="min-h-screen">
            @if($isAdmin)
                {{-- Mobile backdrop overlay --}}
                <div x-data="{ open: false }" 
                     @toggle-sidebar.window="open = !open"
                     x-show="open"
                     x-cloak
                     @click="$dispatch('toggle-sidebar')"
                     class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden transition-opacity duration-300"></div>
                
                @include('partials.sidebar')
            @endif

            @if($isAdmin)
                @if(isset($header))
                    {{ $header }}
                @else
                    <header class="lg:ml-[280px] sticky top-2 z-30">
                        <div class="mx-auto flex items-center justify-between px-4 sm:px-6 lg:px-8 py-3">
                            <div class="flex items-center gap-3">
                                <button @click="$dispatch('toggle-sidebar')" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    </svg>
                                </button>
                                <h1 class="text-lg sm:text-xl font-semibold truncate">@yield('title', __('app.admin.dashboard_title'))</h1>
                            </div>
                            <div class="h-8 w-8 sm:h-9 sm:w-9 rounded-full bg-[#ffb400] flex-shrink-0"></div>
                        </div>
                    </header>
                @endif
            @endif

            <main class="{{ $isAdmin ? 'lg:ml-[280px] p-4 sm:p-6 lg:p-10' : '' }}">
                <div class="max-w-full overflow-x-hidden">
                    @if(isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </div>
            </main>
        </div>

        {{-- Translation Button --}}
        <div id="translation-button-root"></div>

        {{-- Content Modal for displaying items --}}
        @if(!$isAdmin)
            <x-content-modal />
            <x-section-content-modal />
            <x-blogs-modal />
            <x-blog-detail-modal />
            <x-testimonial-detail-modal />
        @endif

        <script>
            // Set initial locale from Laravel
            window.initialLocale = '{{ app()->getLocale() }}';
            
            // Global function to open content modal
            window.openContentModal = function(type, id, slug = null) {
                // Dispatch event to load content
                window.dispatchEvent(new CustomEvent('load-content-modal', { 
                    detail: { type, id, slug } 
                }));
                
                // Dispatch event to open modal
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'content-modal' }));
            };
            
            // Global function to open section content modal
            window.openSectionContentModal = function(sectionId) {
                // Dispatch event to load section content
                window.dispatchEvent(new CustomEvent('load-section-content-modal', { 
                    detail: { sectionId } 
                }));
                
                // Dispatch event to open modal
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'section-content-modal' }));
            };

            // Global function to open testimonial modal
            window.openTestimonialModal = function(testimonialId) {
                // Dispatch event to load testimonial
                window.dispatchEvent(new CustomEvent('load-testimonial-modal', { 
                    detail: { id: testimonialId } 
                }));
                
                // Dispatch event to open modal
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'testimonial-detail-modal' }));
            };

            // Intercept clicks on book-pages, code-summaries, and rooms links
            // Only intercept if the click isn't already handled by React components
            // AND only on non-admin pages
            document.addEventListener('DOMContentLoaded', function() {
                // Check if we're on an admin page
                const isAdminPage = window.location.pathname.startsWith('/admin');
                
                document.addEventListener('click', function(e) {
                    // Skip if on admin page - admin links should work normally
                    if (isAdminPage) {
                        return;
                    }
                    
                    // Skip if already handled by React (check for React event handlers)
                    if (e.target.closest('[data-react-click-handled]')) {
                        return;
                    }
                    
                    // Find the closest link
                    const link = e.target.closest('a');
                    if (!link || !link.href || !window.openContentModal) {
                        return;
                    }
                    
                    // Skip admin links completely
                    if (link.href.includes('/admin/')) {
                        return;
                    }
                    
                    // Skip if it's not a content link
                    if (!link.href.includes('/book-pages/') && 
                        !link.href.includes('/code-summaries/') && 
                        !link.href.includes('/rooms/')) {
                        return;
                    }
                    
                    try {
                        const url = new URL(link.href);
                        const pathname = url.pathname;
                        
                        // Skip if it's just the base path without a slug
                        if (pathname === '/book-pages' || pathname === '/code-summaries' || pathname === '/rooms') {
                            return;
                        }
                        
                        // Check if this is a content link we should intercept
                        let modalType = null;
                        let slug = null;
                        
                        // Match /book-pages/{slug} - must have exactly one slug part
                        const bookPageMatch = pathname.match(/^\/book-pages\/([^\/\?#]+)(?:\/|$|\?|#)/);
                        if (bookPageMatch && bookPageMatch[1]) {
                            modalType = 'book-page';
                            slug = bookPageMatch[1];
                        }
                        // Match /code-summaries/{slug} - must have exactly one slug part
                        else {
                            const codeSummaryMatch = pathname.match(/^\/code-summaries\/([^\/\?#]+)(?:\/|$|\?|#)/);
                            if (codeSummaryMatch && codeSummaryMatch[1]) {
                                modalType = 'code-summary';
                                slug = codeSummaryMatch[1];
                            }
                            // Match /rooms/{slug} - must have exactly one slug part
                            else {
                                const roomMatch = pathname.match(/^\/rooms\/([^\/\?#]+)(?:\/|$|\?|#)/);
                                if (roomMatch && roomMatch[1]) {
                                    modalType = 'room';
                                    slug = roomMatch[1];
                                }
                            }
                        }
                        
                        // If we found a match, open the modal
                        if (modalType && slug && slug.trim() !== '') {
                            // Don't intercept if it's a download/view/visit button
                            if (link.closest('a[href*="download"], a[href*="view"], a[href*="visit"]')) {
                                return;
                            }
                            
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            
                            console.log('Intercepted link click, opening modal - Type:', modalType, 'Slug:', slug);
                            window.openContentModal(modalType, null, slug);
                        }
                    } catch (err) {
                        console.error('Error intercepting link:', err, link.href);
                    }
                }, true); // Use capture phase to catch before navigation
            });
            
            // Blog modal functions
            window.openBlogsModal = function() {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'blogs-modal' }));
            };
            
            window.openBlogModal = function(slug) {
                window.dispatchEvent(new CustomEvent('load-blog-modal', { detail: { slug } }));
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'blog-detail-modal' }));
            };
        </script>
        @stack('scripts')
    </body>
</html>
