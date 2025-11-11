<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Book Pages') }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($categories->count() > 0)
                        <div class="mb-6 flex flex-wrap gap-2">
                            @foreach($categories as $category)
                                <a href="#category-{{ $category->id }}" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-700 text-sm hover:bg-gray-200 dark:hover:bg-gray-600">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    @endif
                    
                    @if($bookPages->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($bookPages as $page)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                    {{-- Badges --}}
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @if($page->status === 'completed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                ✓ Completed
                                            </span>
                                        @elseif($page->status === 'in_progress')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                                ⏳ In Progress
                                            </span>
                                        @endif
                                        @if($page->difficulty)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                                {{ $page->difficulty === 'Beginner' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                                {{ $page->difficulty === 'Intermediate' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                                {{ $page->difficulty === 'Advanced' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}">
                                                {{ $page->difficulty }}
                                            </span>
                                        @endif
                                        @if($page->time_spent)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                ⏱ {{ $page->time_spent }} min
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-lg font-semibold mb-2">
                                        <a href="{{ route('book-pages.show', $page) }}" class="hover:underline">{{ $page->title }}</a>
                                    </h3>
                                    @if($page->book_title || $page->author)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            @if($page->book_title){{ $page->book_title }}@endif
                                            @if($page->author) by {{ $page->author }}@endif
                                        </p>
                                    @endif
                                    @if($page->summary)
                                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 line-clamp-3">{{ $page->summary }}</p>
                                    @endif
                                    @if($page->categories->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            @foreach($page->categories as $category)
                                                <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-xs">{{ $category->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($page->tags->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            @foreach($page->tags->take(5) as $tag)
                                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($page->read_at)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Read: {{ $page->read_at->format('M d, Y') }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $bookPages->links() }}</div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No book pages yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


