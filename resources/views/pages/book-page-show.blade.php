<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $bookPage->title }}</h1>
            <a href="{{ route('book-pages.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Back to Book Pages</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Badges --}}
                    <div class="flex flex-wrap gap-3 mb-6">
                        @if($bookPage->status === 'completed')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Completed
                            </span>
                        @elseif($bookPage->status === 'in_progress')
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                In Progress
                            </span>
                        @endif
                        @if($bookPage->difficulty)
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                                {{ $bookPage->difficulty === 'Beginner' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                {{ $bookPage->difficulty === 'Intermediate' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                {{ $bookPage->difficulty === 'Advanced' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                {{ $bookPage->difficulty }}
                            </span>
                        @endif
                        @if($bookPage->time_spent)
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $bookPage->time_spent }} min
                            </span>
                        @endif
                    </div>

                    @if($bookPage->categories->count() > 0)
                        <div class="mb-4 flex flex-wrap gap-2">
                            @foreach($bookPage->categories as $category)
                                <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-xs">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        @if($bookPage->book_title)
                            <strong>Book:</strong> {{ $bookPage->book_title }}
                            @if($bookPage->author) by {{ $bookPage->author }}@endif
                        @endif
                        @if($bookPage->page_number)
                            <span class="mx-2">|</span>
                            <strong>Page:</strong> {{ $bookPage->page_number }}
                        @endif
                        @if($bookPage->read_at)
                            <span class="mx-2">|</span>
                            <strong>Read:</strong> {{ $bookPage->read_at->format('M d, Y') }}
                        @endif
                    </div>

                    @if($bookPage->references)
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1">References</div>
                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ $bookPage->references }}</div>
                        </div>
                    @endif
                    
                    @if($bookPage->summary)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Summary</h3>
                            <p class="whitespace-pre-wrap">{{ $bookPage->summary }}</p>
                        </div>
                    @endif

                    @if($bookPage->key_objectives)
                        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border-l-4 border-green-500">
                            <h3 class="text-lg font-semibold mb-3">Key Objectives</h3>
                            <ul class="space-y-2">
                                @foreach(explode("\n", $bookPage->key_objectives) as $objective)
                                    @if(trim($objective))
                                        <li class="flex items-start gap-2">
                                            <span class="text-green-600 dark:text-green-400 mt-1">•</span>
                                            <span>{{ trim($objective) }}</span>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($bookPage->reflection)
                        <div class="mb-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border-l-4 border-purple-500">
                            <h3 class="text-lg font-semibold mb-2">Reflection / Insight</h3>
                            <p class="whitespace-pre-wrap">{{ $bookPage->reflection }}</p>
                        </div>
                    @endif

                    @if($bookPage->applied_snippet)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Applied Snippet / Exercise</h3>
                            <div class="bg-gray-900 rounded-lg overflow-hidden">
                                <div class="px-4 py-2 bg-gray-800 border-b border-gray-700 flex items-center gap-2">
                                    <div class="flex gap-1.5">
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    </div>
                                    <span class="text-xs text-gray-400 ml-2">Code / Exercise</span>
                                </div>
                                <pre class="p-4 overflow-x-auto"><code class="text-gray-100 font-mono text-sm whitespace-pre-wrap">{{ $bookPage->applied_snippet }}</code></pre>
                            </div>
                        </div>
                    @endif

                    @if($bookPage->how_to_run)
                        <div class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg border-l-4 border-orange-500">
                            <h3 class="text-lg font-semibold mb-2">How to Run / Recreate</h3>
                            <pre class="whitespace-pre-wrap font-mono text-sm text-gray-700 dark:text-gray-300">{{ $bookPage->how_to_run }}</pre>
                        </div>
                    @endif

                    @if($bookPage->result_evidence)
                        <div class="mb-6 p-4 bg-teal-50 dark:bg-teal-900/20 rounded-lg border-l-4 border-teal-500">
                            <h3 class="text-lg font-semibold mb-2">Result / Evidence</h3>
                            <p class="whitespace-pre-wrap">{{ $bookPage->result_evidence }}</p>
                        </div>
                    @endif
                    
                    @if($bookPage->content)
                        <div class="mb-6 prose dark:prose-invert max-w-none">
                            <h3>Content</h3>
                            <div class="whitespace-pre-wrap">{{ $bookPage->content }}</div>
                        </div>
                    @endif
                    
                    @if($bookPage->tags->count() > 0)
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach($bookPage->tags as $tag)
                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


