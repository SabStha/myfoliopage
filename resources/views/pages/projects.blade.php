<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Projects') }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 flex items-center gap-3">
                        @if(!empty($activeTag))
                            <span class="text-sm">Filtering by tag:</span>
                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs">{{ $activeTag }}</span>
                            <a href="{{ route('projects') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Clear</a>
                        @endif
                    </div>
                    @if(isset($projects) && $projects->count())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($projects as $project)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold mb-1">
                                        <a href="{{ route('project.show', $project->slug) }}" class="hover:underline">{{ $project->getTranslated('title') }}</a>
                                    </h3>
                                    @php($cover = $project->media()->where('type','image')->first())
                                    @if($cover)
                                        <img src="{{ asset('storage/'.$cover->path) }}" alt="{{ $project->getTranslated('title') }}" class="mb-2 rounded">
                                    @endif
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ $project->getTranslated('summary') }}</p>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $project->tech_stack }}</div>
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @foreach(($project->tags ?? []) as $tag)
                                            <a href="{{ route('projects', ['tag' => $tag->slug]) }}" class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs hover:underline">{{ $tag->name }}</a>
                                        @endforeach
                                    </div>
                                    <div class="flex items-center gap-3 text-sm">
                                        @if($project->repo_url)
                                            <a href="{{ $project->repo_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">Repo</a>
                                        @endif
                                        @if($project->demo_url)
                                            <a href="{{ $project->demo_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">Demo</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $projects->withQueryString()->links() }}</div>
                    @else
                        <p>No projects yet. Add some via seeding or admin later.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


