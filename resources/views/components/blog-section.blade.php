@props(['blogs' => collect([])])

<section id="blog" class="bg-white py-12 sm:py-14 md:py-16 px-4 sm:px-6 md:px-12">
  <style>
    @keyframes blogTitleSlideRight {
      0% {
        opacity: 0;
        transform: translateX(-50px);
      }
      50% {
        opacity: 0.5;
      }
      100% {
        opacity: 1;
        transform: translateX(0);
      }
    }
    
    .blog-title-animate {
      animation: blogTitleSlideRight 1s ease-out forwards;
      opacity: 0;
    }
  </style>
  <div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 sm:mb-10 md:mb-12 gap-4 sm:gap-6">
      <!-- Title -->
      <div class="flex-1">
        <h2 class="blog-title-animate text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-neutral-900 mb-3 sm:mb-4 leading-tight" style="font-family: system-ui, -apple-system, sans-serif;">
          {{ __('app.blog.title') }} @if($blogs->count() > 0)<span class="text-neutral-500">({{ $blogs->count() }})</span>@endif
        </h2>
        <p class="text-sm sm:text-base md:text-lg text-neutral-700">{{ __('app.blog.latest_articles') }}</p>
      </div>

      <!-- View All Link -->
      <div class="flex items-center gap-6">
        <a href="#" onclick="event.preventDefault(); openBlogsModal();" class="text-neutral-900 hover:text-neutral-700 underline font-medium text-xs sm:text-sm md:text-base transition-colors duration-200">{{ __('app.common.view_all') }}</a>
      </div>
    </div>

    <!-- Blog Posts Carousel -->
    <div class="relative">
      <div class="blog-scroll-wrapper overflow-x-auto overflow-y-hidden pb-4" style="scrollbar-width: thin; scrollbar-color: #a3a3a3 #f5f5f5;">
        <style>
          .blog-scroll-wrapper::-webkit-scrollbar {
            height: 6px;
          }
          .blog-scroll-wrapper::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 3px;
          }
          .blog-scroll-wrapper::-webkit-scrollbar-thumb {
            background: #a3a3a3;
            border-radius: 3px;
          }
          .blog-scroll-wrapper::-webkit-scrollbar-thumb:hover {
            background: #808080;
          }
          
          /* Continuous scrolling carousel animation - Left to Right */
          @keyframes scrollBlogLeftToRight {
            0% {
              transform: translateX(-50%);
            }
            100% {
              transform: translateX(0);
            }
          }
          
          .blog-carousel-container {
            display: flex;
            animation: scrollBlogLeftToRight 60s linear infinite;
            will-change: transform;
            pointer-events: auto;
          }
          
          .blog-scroll-wrapper:hover .blog-carousel-container {
            animation-play-state: paused;
          }
          
          .blog-carousel-container > div {
            display: flex;
            gap: 0;
            flex-wrap: nowrap;
            min-width: fit-content;
          }
          
          .blog-card-item {
            box-sizing: border-box;
            flex-shrink: 0 !important;
            flex-grow: 0 !important;
          }
          
          @media (max-width: 640px) {
            .blog-card-item {
              width: 280px !important;
              min-width: 280px !important;
              max-width: 280px !important;
            }
          }
        </style>
        <div class="blog-carousel-container" style="width: 200%; display: flex;">
          @php
            // Get blogs from component props (already defined above)
            $blogPosts = $blogs->map(function($blog) {
              $image = $blog->media->where('type', 'image')->first();
              $imageUrl = $image ? asset('storage/' . $image->path) : asset('storage/certficates/certificate-1.jpg');
              $publishedAt = $blog->published_at ? $blog->published_at : $blog->created_at;
              return [
                'title' => $blog->getTranslated('title'),
                'excerpt' => $blog->getTranslated('excerpt') ?? substr(strip_tags($blog->getTranslated('content') ?? ''), 0, 150) . '...',
                'date' => $publishedAt->format('M d, Y'),
                'category' => $blog->category ?? 'Uncategorized',
                'image' => $imageUrl,
                'slug' => $blog->slug
              ];
            })->toArray();
            
            // If no blogs, show empty state or fallback
            if (empty($blogPosts)) {
              $blogPosts = [];
            }
            
            $totalBlogWidth = count($blogPosts) * 296; // Approximate width per card with margin (280px card + 16px margin)
          @endphp
          
          <!-- First set of blog posts -->
          @if(count($blogPosts) > 0)
          <div class="flex" style="width: 50%; gap: 0; flex-shrink: 0; min-width: {{ $totalBlogWidth }}px;">
          @foreach($blogPosts as $index => $post)
        <div onclick="openBlogModal('{{ $post['slug'] }}')" class="blog-card-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden group cursor-pointer border border-neutral-200" style="width: 280px !important; min-width: 280px !important; max-width: 280px !important; margin-right: 16px !important; position: relative !important; z-index: 1 !important; isolation: isolate !important; flex: 0 0 296px !important; box-sizing: border-box !important;">
          <!-- Image -->
          <div class="w-full h-40 sm:h-48 md:h-56 overflow-hidden bg-neutral-100">
            <img 
              src="{{ $post['image'] }}" 
              alt="{{ $post['title'] }}"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
              onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            />
            <div class="w-full h-full hidden items-center justify-center bg-gradient-to-br from-neutral-200 to-neutral-300">
              <svg class="w-12 h-12 sm:w-16 sm:h-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
              </svg>
            </div>
          </div>
          
          <!-- Content -->
          <div class="p-4 sm:p-6">
            <!-- Category and Date -->
            <div class="flex items-center justify-between mb-2 sm:mb-3">
              <span class="text-[10px] sm:text-xs font-semibold text-neutral-600 uppercase tracking-wide">{{ $post['category'] }}</span>
              <span class="text-[10px] sm:text-xs text-neutral-500">{{ $post['date'] }}</span>
            </div>
            
            <!-- Title -->
            <h3 class="text-base sm:text-lg md:text-xl font-bold text-neutral-900 mb-2 sm:mb-3 group-hover:text-neutral-700 transition-colors duration-200 line-clamp-2">
              {{ $post['title'] }}
            </h3>
            
            <!-- Excerpt -->
            <p class="text-xs sm:text-sm text-neutral-600 leading-relaxed mb-3 sm:mb-4 line-clamp-3">
              {{ $post['excerpt'] }}
            </p>
            
            <!-- Read More -->
            <div class="flex items-center text-xs sm:text-sm font-medium text-neutral-900 group-hover:text-neutral-700 transition-colors duration-200">
              <span>{{ __('app.blog.read_more') }}</span>
              <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-2 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
        </div>
      @endforeach
          </div>
          
          <!-- Duplicated set for seamless loop -->
          <div class="flex" style="width: 50%; gap: 0; flex-shrink: 0; min-width: {{ $totalBlogWidth }}px;">
          @foreach($blogPosts as $index => $post)
        <div onclick="openBlogModal('{{ $post['slug'] }}')" class="blog-card-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden group cursor-pointer border border-neutral-200" style="width: 280px !important; min-width: 280px !important; max-width: 280px !important; margin-right: 16px !important; position: relative !important; z-index: 1 !important; isolation: isolate !important; flex: 0 0 296px !important; box-sizing: border-box !important;">
          <!-- Image -->
          <div class="w-full h-40 sm:h-48 md:h-56 overflow-hidden bg-neutral-100">
            <img 
              src="{{ $post['image'] }}" 
              alt="{{ $post['title'] }}"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
              onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            />
            <div class="w-full h-full hidden items-center justify-center bg-gradient-to-br from-neutral-200 to-neutral-300">
              <svg class="w-12 h-12 sm:w-16 sm:h-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
              </svg>
            </div>
          </div>
          
          <!-- Content -->
          <div class="p-4 sm:p-6">
            <!-- Category and Date -->
            <div class="flex items-center justify-between mb-2 sm:mb-3">
              <span class="text-[10px] sm:text-xs font-semibold text-neutral-600 uppercase tracking-wide">{{ $post['category'] }}</span>
              <span class="text-[10px] sm:text-xs text-neutral-500">{{ $post['date'] }}</span>
            </div>
            
            <!-- Title -->
            <h3 class="text-base sm:text-lg md:text-xl font-bold text-neutral-900 mb-2 sm:mb-3 group-hover:text-neutral-700 transition-colors duration-200 line-clamp-2">
              {{ $post['title'] }}
            </h3>
            
            <!-- Excerpt -->
            <p class="text-xs sm:text-sm text-neutral-600 leading-relaxed mb-3 sm:mb-4 line-clamp-3">
              {{ $post['excerpt'] }}
            </p>
            
            <!-- Read More -->
            <div class="flex items-center text-xs sm:text-sm font-medium text-neutral-900 group-hover:text-neutral-700 transition-colors duration-200">
              <span>{{ __('app.blog.read_more') }}</span>
              <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-2 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
        </div>
      @endforeach
          </div>
          @else
          <!-- Empty state -->
          <div class="flex items-center justify-center py-16">
            <div class="text-center">
              <p class="text-neutral-500 text-lg">No blog posts available yet.</p>
              @auth
                <a href="{{ route('admin.blogs.create') }}" class="mt-4 inline-block text-amber-600 hover:text-amber-700 underline">Create your first blog post</a>
              @endauth
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

<script>
    function openBlogModal(slug) {
        window.dispatchEvent(new CustomEvent('load-blog-modal', { detail: { slug } }));
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'blog-detail-modal' }));
    }
</script>
