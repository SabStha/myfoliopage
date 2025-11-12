<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Code Summaries') }}</h2>
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
                    
                    @if($codeSummaries->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($codeSummaries as $summary)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold mb-2">
                                        <a href="{{ route('code-summaries.show', $summary) }}" class="hover:underline">{{ $summary->title }}</a>
                                    </h3>
                                    @if($summary->language)
                                        <span class="inline-block px-2 py-0.5 rounded bg-green-100 dark:bg-green-900 text-xs mb-2">{{ $summary->language }}</span>
                                    @endif
                                    @if($summary->summary)
                                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 line-clamp-3">{{ $summary->summary }}</p>
                                    @endif
                                    @if($summary->categories->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            @foreach($summary->categories as $category)
                                                <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-xs">{{ $category->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($summary->repository_url)
                                        <a href="{{ $summary->repository_url }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View Repository</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $codeSummaries->links() }}</div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No code summaries yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>









