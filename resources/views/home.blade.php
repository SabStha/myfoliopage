@extends('layouts.app')
@section('title','Home')
@section('content')
<div class="min-h-screen snap-y snap-mandatory overflow-y-auto">
  {{-- HERO --}}
  <section id="hero" class="relative snap-start min-h-[85vh] sm:h-[85vh] flex flex-col" style="background-color: {{ ($heroSection->background_color ?? '#e0e7ff') }}; overflow-x: visible; overflow-y: visible;">
  <style>
    @keyframes blobPulse {
      0% { transform: translateX(0) scale(1.00); filter: drop-shadow(0 10px 20px rgba(0,0,0,0.10)); }
      50% { transform: translateX(-2.2%) scale(1.05); filter: drop-shadow(0 16px 28px rgba(0,0,0,0.14)); }
      100% { transform: translateX(1.2%) scale(1.03); filter: drop-shadow(0 14px 26px rgba(0,0,0,0.12)); }
    }
    .animate-blob { 
      animation: blobPulse 5s ease-in-out infinite alternate; 
      transform-origin: 80% 50%;
      will-change: transform, filter;
      isolation: isolate; /* Isolate the animation so it doesn't affect siblings */
    }
    .hover-lift { transition: transform .3s ease-in-out, box-shadow .3s ease-in-out; }
    .hover-lift:hover { transform: translateY(-6px) scale(1.03); box-shadow: 0 20px 30px rgba(0,0,0,.15); }
    .btn-lift { transition: transform .2s ease-in-out, box-shadow .2s ease-in-out, filter .2s ease-in-out; }
    .btn-lift:hover { transform: translateY(-2px); box-shadow: 0 10px 16px rgba(0,0,0,.12); filter: brightness(0.98); }
    #typed-head { position: relative; white-space: pre-wrap; word-wrap: break-word; }
    @keyframes caretBlink { 0%,100% { opacity: 1 } 50% { opacity: 0 } }
    #typed-head::after { content: '▌'; margin-left: 2px; animation: caretBlink 1s step-end infinite; color: #111; }
    .headline { display: inline-block; background-image: linear-gradient(currentColor, currentColor); background-size: 0% 2px; background-repeat: no-repeat; background-position: left 100%; transition: background-size .4s ease-in-out, color .3s ease-in-out, transform .3s ease-in-out; }
    .headline:hover { background-size: 100% 2px; color: #0f172a; transform: translateY(-2px); }
    /* Blob hover interaction */
    .blob-interactive { 
      transition: transform .45s ease-in-out, filter .45s ease-in-out; 
      will-change: transform, filter;
      isolation: isolate; /* Prevent affecting other elements */
    }
    .blob-hover:hover .blob-interactive { 
      transform: scale(1.10) translateX(-2%); 
      filter: drop-shadow(0 20px 36px rgba(0,0,0,.16)); 
    }
    /* Lock text and image container positions - but allow transforms */
    .hero-text-container { 
      position: relative !important; 
      isolation: isolate;
      overflow: hidden; /* Prevent content overflow */
      max-width: 100%;
      width: 100%;
    }
    .hero-image-container { 
      position: absolute !important; 
      isolation: isolate;
      will-change: auto;
      max-width: 100%;
    }
    /* Ensure images don't get cut off */
    .hero-image-container img {
      object-position: center !important;
      max-width: 100%;
      height: auto;
    }
    /* When reversed, ensure left-aligned positioning works with padding */
    .hero-layout-reversed .hero-image-container {
      left: 0.5rem !important; /* Minimal padding for very small screens */
    }
    @media (min-width: 375px) {
      .hero-layout-reversed .hero-image-container {
        left: 1rem !important; /* Slightly more padding for small screens */
      }
    }
    @media (min-width: 640px) {
      .hero-layout-reversed .hero-image-container {
        left: 2rem !important; /* Restore padding for larger screens */
      }
    }
    /* Prevent parent containers from clipping the image */
    .blob-hover {
      overflow-x: visible !important;
      overflow-y: visible !important;
      max-width: 100%;
    }
    /* Responsive offsets - disable on very small screens */
    @media (max-width: 640px) {
      .hero-text-container[style*="translateX"] {
        transform: translateX(0) !important;
      }
      .hero-image-container[style*="translateX"] {
        transform: translateX(0) !important;
      }
      .hero-badge-offset[style*="translateX"] {
        transform: translateX(0) !important;
      }
    }
    /* Ensure blob doesn't overflow on small screens */
    @media (max-width: 640px) {
      .blob-hover svg {
        max-width: 100%;
        overflow: hidden;
      }
    }
    @media (prefers-reduced-motion: reduce) {
      .animate-blob, .hover-lift, .btn-lift { animation: none !important; transition: none !important; }
    }
    /* Ensure grid order works for layout reversal */
    @media (min-width: 1024px) {
      .hero-layout-container {
        display: grid !important;
      }
      .hero-layout-container > [data-order="1"] {
        order: 1;
      }
      .hero-layout-container > [data-order="2"] {
        order: 2;
      }
    }
  </style>
   @if(($heroSection->nav_visible ?? true) && !empty($heroSection->navigation_links))
   <nav class="absolute right-3 top-3 sm:right-6 sm:top-6 z-30 hidden md:flex items-center gap-4 lg:gap-10 text-gray-700 font-medium text-sm lg:text-base">
     @foreach($heroSection->getTranslatedNavigationLinks() as $navLink)
       <a href="#{{ $navLink['section_id'] ?? '' }}" 
          onclick="event.preventDefault(); document.getElementById('{{ $navLink['section_id'] ?? '' }}').scrollIntoView({ behavior: 'smooth', block: 'start' });" 
          class="hover:text-gray-900 transition-colors">
         {{ $navLink['text'] ?? 'Link' }}
       </a>
     @endforeach
   </nav>
  @endif

  @php
    $isReversed = isset($heroSection) && $heroSection->layout_reversed === true;
  @endphp
  <div class="mx-auto max-w-7xl {{ $isReversed ? 'pl-2 pr-2 sm:pl-4 sm:pr-4 md:pl-8 md:pr-6' : 'px-2 sm:px-4 md:px-6' }} pt-3 pb-2 sm:pt-4 sm:pb-2 md:pt-6 md:pb-2 lg:pt-8 lg:pb-2 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 md:gap-8 items-center flex-1 hero-layout-container" style="overflow-x: hidden; position: relative; width: 100%; max-width: 100%;">
   @php
      $textOffset = isset($heroSection) && isset($heroSection->text_horizontal_offset) ? (int)$heroSection->text_horizontal_offset : 0;
      $imageOffset = isset($heroSection) && isset($heroSection->image_horizontal_offset) ? (int)$heroSection->image_horizontal_offset : 0;
      $badgeOffset = isset($heroSection) && isset($heroSection->badge_horizontal_offset) ? (int)$heroSection->badge_horizontal_offset : 0;
      
      // Store default badge offset for restoration
      $defaultBadgeOffset = $badgeOffset;
      
      // When reversed, switch to left-alignment for image and badge
      // This makes them use the left space instead of right space
      if ($isReversed) {
        // Profile pic: Position from left with padding (left-8 = 2rem) and adjust offset
        // Move image to use left space but with padding from edge
        $imageOffset = $imageOffset - 150; // Move left to use left space with padding
        
        // Text: Small adjustment to the right to balance
        $textOffset = $textOffset + 10; // Move slightly right for balance
        
        // Badge: When reversed, use default offset (restore default position)
        // The badge is inside the text container, so it moves with the text container
        // Keep the default offset to maintain proper positioning relative to text
        $badgeOffset = $defaultBadgeOffset;
      } else {
        // When not reversed, use default badge offset (restore default position)
        $badgeOffset = $defaultBadgeOffset;
      }
      
      $textOrder = $isReversed ? 2 : 1;
      $imageOrder = $isReversed ? 1 : 2;
    @endphp
    <div class="relative z-20 hero-text-container w-full px-1 sm:px-2 md:px-0" style="transform: translateX({{ $textOffset }}px);" data-order="{{ $textOrder }}">
       <p class="uppercase tracking-widest text-[10px] min-[375px]:text-xs sm:text-sm font-semibold mb-2 sm:mb-3 md:mb-4 relative z-10 hero-badge-offset" style="color: {{ $heroSection->badge_color ?? '#ffb400' }}; transform: translateX({{ $badgeOffset }}px);">{{ $heroSection ? $heroSection->getTranslated('badge_text') : __('app.hero.badge') }}</p>
      <h1 class="{{ $heroSection->heading_size_mobile ?? 'text-2xl' }} {{ $heroSection->heading_size_tablet ?? 'min-[375px]:text-3xl sm:text-4xl md:text-5xl' }} {{ $heroSection->heading_size_desktop ?? 'lg:text-6xl' }} font-extrabold leading-tight text-gray-900 break-words hyphens-auto">
        <span id="typed-head" class="headline" aria-label="{{ $heroSection ? $heroSection->getTranslated('heading_text') : __('app.hero.title') }}"></span>
      </h1>
      <p class="mt-3 sm:mt-4 md:mt-6 text-xs min-[375px]:text-sm sm:text-base text-gray-500 max-w-full sm:max-w-xl">{{ $heroSection ? $heroSection->getTranslated('subheading_text') : __('app.hero.description') }}</p>
       <div class="mt-4 sm:mt-6 md:mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 md:gap-4">
        @if(($heroSection->button1_visible ?? true))
        <a href="{{ $heroSection->button1_link ?? route('projects') }}" class="inline-flex items-center justify-center px-3 py-2 min-[375px]:px-4 min-[375px]:py-2.5 sm:px-5 sm:py-3 rounded-md font-semibold shadow btn-lift text-xs min-[375px]:text-sm sm:text-base w-full sm:w-auto" style="background-color: {{ $heroSection->button1_bg_color ?? '#ffb400' }}; color: {{ $heroSection->button1_text_color ?? '#111827' }};">{{ $heroSection ? $heroSection->getTranslated('button1_text') : __('app.nav.projects') }}</a>
        @endif
        @if(($heroSection->button2_visible ?? true))
        <a href="{{ $heroSection->button2_link ?? 'https://www.linkedin.com/in/...' }}" class="inline-flex items-center justify-center px-3 py-2 min-[375px]:px-4 min-[375px]:py-2.5 sm:px-5 sm:py-3 rounded-md border font-semibold btn-lift hover:bg-gray-50 text-xs min-[375px]:text-sm sm:text-base w-full sm:w-auto" style="background-color: {{ $heroSection->button2_bg_color ?? '#ffffff' }}; color: {{ $heroSection->button2_text_color ?? '#1f2937' }}; border-color: {{ $heroSection->button2_border_color ?? '#d1d5db' }};">{{ $heroSection ? $heroSection->getTranslated('button2_text') : 'LinkedIn' }}</a>
        @endif
       </div>
     </div>

   <div class="relative h-[200px] min-[375px]:h-[240px] min-[425px]:h-[280px] sm:h-[320px] md:h-[400px] lg:h-[460px] xl:h-[520px] blob-hover {{ $isReversed ? 'hero-layout-reversed' : '' }} w-full" style="position: relative; overflow: hidden; max-width: 100%;" data-order="{{ $imageOrder }}">
      @if(($heroSection->blob_visible ?? true))
      @php
        $blobOffset = $isReversed ? ($imageOffset * 3) : 0;
        // SVG blob path
        $blobPath = "M818.6,63.4c47.7,44.7,76.8,114,69.6,179.8c-7.2,65.9-50.8,127.9-107.3,164.9 c-56.5,37-125.8,49-196.7,52.9c-70.9,4-143.5,0-204.7-33.6c-61.2-33.7-111-96-129.6-164.8c-18.7-68.8-6.2-143.9,36.7-190.3 C330,26,410.3,9.7,485.9,14.8c75.6,5.1,146.6,31.7,219.2,49.6C777.7,82.2,803.4,49.1,818.6,63.4z";
      @endphp
      <div class="absolute top-0 bottom-0 {{ $isReversed ? '' : 'right-0 sm:right-4 md:right-8 lg:right-16 xl:right-32' }} z-10 overflow-hidden" style="{{ $isReversed ? 'left: 0 !important; right: auto !important; transform: translateX(' . $blobOffset . 'px);' : '' }} max-width: 100%;">
        <svg class="w-[120%] sm:w-[130%] md:w-[140%] lg:w-[150%] h-full animate-blob blob-interactive" viewBox="0 0 900 700" preserveAspectRatio="xMidYMid slice" aria-hidden="true" style="pointer-events: none; max-width: none;">
           <path fill="{{ $heroSection->blob_color ?? '#ffb400' }}" d="{{ $blobPath }}"/>
        </svg>
      </div>
      @endif
      <div class="absolute {{ $isReversed ? 'left-0 sm:left-2 md:left-4 lg:left-8' : 'right-0 sm:right-2 md:right-4 lg:right-8' }} top-4 sm:top-6 md:top-8 lg:top-12 z-20 hero-image-container w-full max-w-[calc(100%-1rem)] sm:max-w-none" style="transform: translateX({{ $imageOffset }}px);">
        <div class="rounded-[16px] sm:rounded-[20px] md:rounded-[24px] lg:rounded-[28px] overflow-hidden relative mx-auto" style="max-width: 100%;">
          <div class="relative w-full max-w-[200px] min-[320px]:max-w-[220px] min-[375px]:max-w-[240px] min-[425px]:max-w-[280px] sm:max-w-[320px] md:max-w-[360px] lg:max-w-[400px] xl:max-w-[460px] aspect-[3/4] mx-auto">
            @if(!empty($finalProfileImages))
              @foreach($finalProfileImages as $index => $imageUrl)
                <img 
                  src="{{ $imageUrl }}" 
                  alt="Profile {{ $index + 1 }}" 
                  class="absolute inset-0 w-full h-full object-cover object-center select-none profile-image {{ $index === 0 ? 'opacity-100' : 'opacity-0' }} transition-opacity duration-1000 ease-in-out" 
                  data-index="{{ $index }}"
                  style="object-position: center;"
                  onerror="this.style.display='none';"
                />
              @endforeach
            @else
              <img src="{{ ($profile && $profile->photo_path) ? asset('storage/'.$profile->photo_path) : asset('images/profile_main.png') }}" alt="Profile" class="w-full h-full object-cover object-center select-none" style="object-position: center;" onerror="this.src='{{ asset('images/profile_main.png') }}';" />
            @endif
          </div>
         </div>
       </div>
     </div>
   </div>
   
   <!-- Scroll affordance at bottom right of hero section -->
   <div class="absolute bottom-2 right-2 min-[375px]:bottom-3 min-[375px]:right-3 sm:bottom-4 sm:right-4 md:bottom-6 md:right-6 lg:bottom-8 lg:right-8 z-30 flex flex-col items-center gap-0.5 min-[375px]:gap-1 sm:gap-1.5 text-gray-500 pointer-events-none">
     <span class="text-[8px] min-[375px]:text-[10px] sm:text-xs md:text-sm font-semibold uppercase tracking-wider text-gray-600 animate-bounce [animation-delay:0.1s]">{{ __('app.common.scroll') }}</span>
     <svg class="h-4 w-4 min-[375px]:h-5 min-[375px]:w-5 sm:h-6 sm:w-6 md:h-8 md:w-8 animate-bounce text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
       <path d="M12 5v14"/>
       <path d="M19 12l-7 7-7-7"/>
     </svg>
   </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var el = document.getElementById('typed-head');
      if (!el) return;
      var full = el.getAttribute('aria-label') || '';
      var i = 0; var speed = 55; var typing = false; var timer;
      function step() {
        if (i <= full.length) {
          el.textContent = full.slice(0, i);
          i++;
          timer = setTimeout(step, speed);
        } else { typing = false; }
      }
      function startTyping(reset) {
        if (typing) return; // avoid overlapping
        if (reset) { clearTimeout(timer); i = 0; }
        typing = true; step();
      }
      // initial slow typing only
      startTyping(true);

      // Profile image rotation
      var profileImages = document.querySelectorAll('.profile-image');
      if (profileImages.length > 1) {
        var currentIndex = 0;
        var rotationInterval = {{ ($heroSection->image_rotation_interval ?? 2000) }};
        var imageInterval = setInterval(function() {
          // Fade out current image
          profileImages[currentIndex].classList.remove('opacity-100');
          profileImages[currentIndex].classList.add('opacity-0');
          
          // Move to next image
          currentIndex = (currentIndex + 1) % profileImages.length;
          
          // Fade in next image
          profileImages[currentIndex].classList.remove('opacity-0');
          profileImages[currentIndex].classList.add('opacity-100');
        }, rotationInterval);
      }
    });
  </script>
 </section>

  {{-- DISCOVER BAND --}}
  <section id="discover" class="snap-start pb-8 md:pb-10 lg:pb-12" style="padding-bottom: 3rem;">
    <x-engagement.teaser
      :title="$engagementSection->getTranslated('title') ?? 'Discover our engagements'"
      :video="$engagementVideo ?? asset('storage/videos/engagement-01.mp4')"
    />
  </section>

  {{-- MY WORKS SECTION --}}
  <section id="my-works" class="snap-start">
      <script>
        // Pass translations to React components
        window.translations = {
            progress: {
                my: @json(__('app.progress.my')),
                ongoing: @json(__('app.progress.ongoing')),
                progress: @json(__('app.progress.progress')),
                category: @json(__('app.progress.category')),
                status: @json(__('app.progress.status')),
                footnote: @json(__('app.progress.footnote')),
                no_subsections: @json(__('app.progress.no_subsections')),
                no_subsections_configured: @json(__('app.progress.no_subsections_configured')),
                no_categories: @json(__('app.progress.no_categories')),
                select_category: @json(__('app.progress.select_category')),
                no_items: @json(__('app.progress.no_items')),
            }
        };
        // Pass certificates, courses, rooms, badges, games, simulations, and programs data to React component - MUST be before React component mounts
        window.certificatesData = @json($certificatesData ?? []);
        window.coursesData = @json($coursesData ?? []);
        window.roomsData = @json($roomsData ?? []);
        window.badgesData = @json($badgesData ?? []);
        window.gamesData = @json($gamesData ?? []);
        window.simulationsData = @json($simulationsData ?? []);
        window.programsData = @json($programsData ?? []);
        window.progressItemsData = @json($progressItems ?? []);
        window.homePageSections = @json($homePageSections ?? []);
        console.log('Home page: certificatesData set:', window.certificatesData);
        console.log('Home page: coursesData set:', window.coursesData);
        console.log('Home page: roomsData set:', window.roomsData);
        console.log('Home page: badgesData set:', window.badgesData);
        console.log('Home page: gamesData set:', window.gamesData);
        console.log('Home page: simulationsData set:', window.simulationsData);
        console.log('Home page: programsData set:', window.programsData);
        console.log('Home page: progressItemsData set:', window.progressItemsData);
        console.log('Home page: homePageSections set:', window.homePageSections);
      </script>
    <div id="my-works-root"></div>
  </section>

  {{-- BLOG SECTION --}}
  <x-blog-section :blogs="$blogs" />

  {{-- TESTIMONIALS SECTION --}}
  <x-testimonials-section />

  {{-- FOOTER --}}
  <x-footer />
</div>
@endsection

