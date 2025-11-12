<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $codeSummary->title }}</h1>
            <a href="{{ route('code-summaries.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Back to Code Summaries</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($codeSummary->categories->count() > 0)
                        <div class="mb-4 flex flex-wrap gap-2">
                            @foreach($codeSummary->categories as $category)
                                <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-xs">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="mb-4 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                        @if($codeSummary->language)
                            <span><strong>Language:</strong> {{ $codeSummary->language }}</span>
                        @endif
                        @if($codeSummary->file_path)
                            <span><strong>File:</strong> {{ $codeSummary->file_path }}</span>
                        @endif
                        @if($codeSummary->repository_url)
                            <a href="{{ $codeSummary->repository_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">View Repository</a>
                        @endif
                    </div>
                    
                    @if($codeSummary->code)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Code</h3>
                            <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-x-auto"><code>{{ $codeSummary->code }}</code></pre>
                        </div>
                    @endif
                    
                    @if($codeSummary->summary)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Summary</h3>
                            <p class="whitespace-pre-wrap">{{ $codeSummary->summary }}</p>
                        </div>
                    @endif
                    
                    @if($codeSummary->tags->count() > 0)
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach($codeSummary->tags as $tag)
                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>








