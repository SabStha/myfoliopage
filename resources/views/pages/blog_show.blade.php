<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $blog->getTranslated('title') }}</h1>
            <a href="{{ route('home') }}#blog" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ __('app.blog.back_to_blog') }}</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <article class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Featured Image -->
                @if($blog->media->where('type', 'image')->first())
                    <div class="w-full h-64 md:h-96 overflow-hidden">
                        <img 
                            src="{{ asset('storage/' . $blog->media->where('type', 'image')->first()->path) }}" 
                            alt="{{ $blog->getTranslated('title') }}"
                            class="w-full h-full object-cover"
                        />
                    </div>
                @endif
                
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <!-- Meta Information -->
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                            @if($blog->category)
                                <span class="font-semibold text-neutral-600 uppercase tracking-wide">{{ $blog->category }}</span>
                            @endif
                            @if($blog->published_at)
                                <span>{{ $blog->published_at->format('F d, Y') }}</span>
                            @endif
                        </div>
                        @if($blog->linkedin_url)
                            <a href="{{ $blog->linkedin_url }}" target="_blank" class="text-blue-600 hover:underline text-sm">
                                {{ __('app.blog.view_on_linkedin') }}
                            </a>
                        @endif
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $blog->getTranslated('title') }}</h1>
                    
                    <!-- Excerpt -->
                    @if($blog->getTranslated('excerpt'))
                        <p class="text-xl text-gray-600 dark:text-gray-300 mb-6 italic">{{ $blog->getTranslated('excerpt') }}</p>
                    @endif
                    
                    <!-- Tags -->
                    @if($blog->tags->count() > 0)
                        <div class="mb-6 flex flex-wrap gap-2">
                            @foreach($blog->tags as $tag)
                                <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-sm">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                    
                    <!-- Content -->
                    <div class="prose prose-lg dark:prose-invert max-w-none">
                        @if($blog->content)
                            <div class="blog-content">
                                {!! nl2br(e($blog->getTranslated('content'))) !!}
                            </div>
                        @else
                            <p class="text-gray-600 dark:text-gray-400">{{ $blog->getTranslated('excerpt') }}</p>
                        @endif
                    </div>
                    
                    <!-- Share Section -->
                    @if($blog->linkedin_url)
                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('app.blog.originally_posted_on') }}</p>
                            <a href="{{ $blog->linkedin_url }}" target="_blank" class="text-blue-600 hover:underline">
                                {{ __('app.blog.linkedin_post') }} →
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        </div>
    </div>
</x-app-layout>


