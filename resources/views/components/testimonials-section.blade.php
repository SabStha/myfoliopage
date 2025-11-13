<section id="testimonials" class="bg-neutral-100 py-12 sm:py-14 md:py-16 px-4 sm:px-6 md:px-12">
  <div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8 sm:mb-10 md:mb-12">
      <!-- Title and Subtitle -->
      <div>
        <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-neutral-900 mb-2 leading-tight" style="font-family: system-ui, -apple-system, sans-serif;">
          <span class="block">{{ __('app.testimonials.recognized_by') }}</span>
          <span class="block text-4xl sm:text-5xl md:text-6xl lg:text-7xl">{{ __('app.testimonials.the_minds_i_respect') }}</span>
        </h2>
        <p class="text-sm sm:text-base md:text-lg text-neutral-700 mt-2 sm:mt-3">{{ __('app.testimonials.subtitle') }}</p>
      </div>
    </div>

    <!-- Testimonials Carousel -->
    <div class="relative">
      <div class="testimonials-scroll-wrapper overflow-x-auto overflow-y-hidden pb-4" style="scrollbar-width: thin; scrollbar-color: #a3a3a3 #f5f5f5;">
        <style>
          .testimonials-scroll-wrapper::-webkit-scrollbar {
            height: 6px;
          }
          .testimonials-scroll-wrapper::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 3px;
          }
          .testimonials-scroll-wrapper::-webkit-scrollbar-thumb {
            background: #a3a3a3;
            border-radius: 3px;
          }
          .testimonials-scroll-wrapper::-webkit-scrollbar-thumb:hover {
            background: #808080;
          }
          
          /* Continuous scrolling carousel animation - Left direction */
          @keyframes scrollTestimonialsLeft {
            0% {
              transform: translateX(0);
            }
            100% {
              transform: translateX(-50%);
            }
          }
          
          .testimonials-carousel-container {
            display: flex;
            animation: scrollTestimonialsLeft 80s linear infinite;
            will-change: transform;
            pointer-events: auto;
          }
          
          .testimonials-carousel-container > div {
            display: flex;
            gap: 0;
            align-items: stretch;
            flex-wrap: nowrap;
            min-width: fit-content;
          }
          
          .testimonial-card {
            box-sizing: border-box;
            flex-shrink: 0 !important;
            flex-grow: 0 !important;
          }
          
          .testimonials-scroll-wrapper:hover .testimonials-carousel-container {
            animation-play-state: paused;
          }
          
          /* Hover effects for testimonial cards */
          .testimonial-card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            cursor: pointer;
            position: relative;
            z-index: 1;
            isolation: isolate;
          }
          
          .testimonial-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            z-index: 10;
          }
          
          .testimonial-card:hover .testimonial-image {
            transform: none;
          }
          
          .testimonial-image {
            transition: transform 0.5s ease-in-out;
            object-fit: contain;
            object-position: center;
            background-color: #f5f5f5;
          }
        </style>
        <div class="testimonials-carousel-container" style="width: 200%; display: flex;">
          @php
            $testimonials = \App\Models\Testimonial::published()->ordered()->with('media')->get();
            
            // If no testimonials, show empty state
            if ($testimonials->isEmpty()) {
              $testimonials = collect([]);
            }
            
            $totalTestimonialsWidth = $testimonials->count() > 0 ? $testimonials->count() * 296 : 0; // 280px width + 16px margin per card
          @endphp
          
          @if($testimonials->isEmpty())
            <div class="w-full py-12 text-center">
              <p class="text-neutral-600">No testimonials available yet.</p>
            </div>
          @else
            <!-- First set of testimonials -->
            <div class="flex" style="width: 50%; gap: 0; flex-shrink: 0; min-width: {{ $totalTestimonialsWidth }}px;">
            @foreach($testimonials as $testimonial)
              @php
                $photoUrl = $testimonial->photo_url;
                if (!$photoUrl) {
                  $imageMedia = $testimonial->media->where('type', 'image')->first();
                  $photoUrl = $imageMedia ? asset('storage/' . $imageMedia->path) : 'https://i.pravatar.cc/150?img=' . ($testimonial->id % 70);
                }
              @endphp
              <div onclick="openTestimonialModal({{ $testimonial->id }})" class="testimonial-card bg-white rounded-lg shadow-sm overflow-hidden flex-shrink-0 cursor-pointer" style="width: 280px !important; min-width: 280px !important; max-width: 280px !important; margin-right: 16px !important; margin-left: 0 !important; position: relative !important; z-index: 1 !important; isolation: isolate !important; flex: 0 0 296px !important; box-sizing: border-box !important;">
                <!-- Large Image -->
                <div class="w-full h-48 sm:h-56 md:h-64 lg:h-72 overflow-hidden" style="position: relative; z-index: 1;">
                  <img 
                    src="{{ $photoUrl }}" 
                    alt="{{ $testimonial->name }}"
                    class="testimonial-image w-full h-full"
                    style="object-fit: contain; object-position: center; background-color: #f5f5f5;"
                    x-on:error="$el.src='https://i.pravatar.cc/150?img=' + Math.floor(Math.random() * 70)"
                  />
                </div>
                
                <!-- Text Section -->
                <div class="p-3 sm:p-4 md:p-5">
                  <div class="mb-2 sm:mb-3">
                    <h3 class="font-bold text-neutral-900 text-xs sm:text-sm mb-0.5">{{ $testimonial->name }}</h3>
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                      @if($testimonial->title)
                        <p class="text-[10px] sm:text-xs text-blue-600 font-medium">{{ $testimonial->title }}</p>
                      @endif
                      @if($testimonial->title && $testimonial->getTranslated('company'))
                        <span class="text-[10px] sm:text-xs text-neutral-400">•</span>
                      @endif
                      @if($testimonial->getTranslated('company'))
                        <p class="text-[10px] sm:text-xs text-neutral-600">{{ $testimonial->getTranslated('company') }}</p>
                      @endif
                    </div>
                  </div>
                  
                  <!-- Quote -->
                  <blockquote class="text-neutral-900 text-xs sm:text-sm leading-relaxed mb-2 sm:mb-3 line-clamp-3">
                    &ldquo;{{ $testimonial->getTranslated('quote') }}&rdquo;
                  </blockquote>
                  
                  <!-- SNS Link -->
                  @if($testimonial->sns_url)
                    <a 
                      href="{{ $testimonial->sns_url }}" 
                      target="_blank"
                      rel="noopener noreferrer"
                      onclick="event.stopPropagation()"
                      class="inline-flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-xs text-blue-600 hover:text-blue-700 hover:underline font-medium"
                    >
                      <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                      </svg>
                      {{ __('app.testimonials.view_profile') }}
                    </a>
                  @endif
                </div>
              </div>
            @endforeach
            </div>
            
            <!-- Duplicated set for seamless loop -->
            <div class="flex" style="width: 50%; gap: 0; flex-shrink: 0; min-width: {{ $totalTestimonialsWidth }}px;">
            @foreach($testimonials as $testimonial)
              @php
                $photoUrl = $testimonial->photo_url;
                if (!$photoUrl) {
                  $imageMedia = $testimonial->media->where('type', 'image')->first();
                  $photoUrl = $imageMedia ? asset('storage/' . $imageMedia->path) : 'https://i.pravatar.cc/150?img=' . ($testimonial->id % 70);
                }
              @endphp
              <div onclick="openTestimonialModal({{ $testimonial->id }})" class="testimonial-card bg-white rounded-lg shadow-sm overflow-hidden flex-shrink-0 cursor-pointer" style="width: 280px !important; min-width: 280px !important; max-width: 280px !important; margin-right: 16px !important; margin-left: 0 !important; position: relative !important; z-index: 1 !important; isolation: isolate !important; flex: 0 0 296px !important; box-sizing: border-box !important;">
                <!-- Large Image -->
                <div class="w-full h-48 sm:h-56 md:h-64 lg:h-72 overflow-hidden" style="position: relative; z-index: 1;">
                  <img 
                    src="{{ $photoUrl }}" 
                    alt="{{ $testimonial->name }}"
                    class="testimonial-image w-full h-full"
                    style="object-fit: contain; object-position: center; background-color: #f5f5f5;"
                    x-on:error="$el.src='https://i.pravatar.cc/150?img=' + Math.floor(Math.random() * 70)"
                  />
                </div>
                
                <!-- Text Section -->
                <div class="p-3 sm:p-4 md:p-5">
                  <div class="mb-2 sm:mb-3">
                    <h3 class="font-bold text-neutral-900 text-xs sm:text-sm mb-0.5">{{ $testimonial->name }}</h3>
                    <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                      @if($testimonial->title)
                        <p class="text-[10px] sm:text-xs text-blue-600 font-medium">{{ $testimonial->title }}</p>
                      @endif
                      @if($testimonial->title && $testimonial->getTranslated('company'))
                        <span class="text-[10px] sm:text-xs text-neutral-400">•</span>
                      @endif
                      @if($testimonial->getTranslated('company'))
                        <p class="text-[10px] sm:text-xs text-neutral-600">{{ $testimonial->getTranslated('company') }}</p>
                      @endif
                    </div>
                  </div>
                  
                  <!-- Quote -->
                  <blockquote class="text-neutral-900 text-xs sm:text-sm leading-relaxed mb-2 sm:mb-3 line-clamp-3">
                    &ldquo;{{ $testimonial->getTranslated('quote') }}&rdquo;
                  </blockquote>
                  
                  <!-- SNS Link -->
                  @if($testimonial->sns_url)
                    <a 
                      href="{{ $testimonial->sns_url }}" 
                      target="_blank"
                      rel="noopener noreferrer"
                      onclick="event.stopPropagation()"
                      class="inline-flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-xs text-blue-600 hover:text-blue-700 hover:underline font-medium"
                    >
                      <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                      </svg>
                      {{ __('app.testimonials.view_profile') }}
                    </a>
                  @endif
                </div>
              </div>
            @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
