<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $project->getTranslated('title') }}</h1>
            <a href="{{ route('projects') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Back to Projects</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach($project->tags as $tag)
                            <a href="{{ route('projects', ['tag' => $tag->slug]) }}" class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs hover:underline">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                    @if($project->media->count())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            @foreach($project->media as $m)
                                @if($m->type === 'image')
                                    <img src="{{ asset('storage/'.$m->path) }}" alt="{{ $project->getTranslated('title') }}" class="rounded" />
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <p class="mb-4">{{ $project->getTranslated('summary') }}</p>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">Tech: {{ $project->tech_stack }}</div>
                    <div class="flex items-center gap-4 text-sm">
                        @if($project->repo_url)
                            <a href="{{ $project->repo_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">Repository</a>
                        @endif
                        @if($project->demo_url)
                            <a href="{{ $project->demo_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">Live Demo</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>






