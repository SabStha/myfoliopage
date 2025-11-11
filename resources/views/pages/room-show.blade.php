<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $room->title }}</h1>
            <a href="{{ route('rooms.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Back to Rooms</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($room->categories->count() > 0)
                        <div class="mb-4 flex flex-wrap gap-2">
                            @foreach($room->categories as $category)
                                <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-xs">{{ $category->getTranslated('name') }}</span>
                            @endforeach
                        </div>
                    @endif
                    
                    <div class="mb-4 flex items-center gap-4 text-sm">
                        @if($room->platform)
                            <span class="px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900 text-xs">{{ $room->platform }}</span>
                        @endif
                        @if($room->difficulty)
                            <span class="px-2 py-0.5 rounded bg-orange-100 dark:bg-orange-900 text-xs">{{ $room->difficulty }}</span>
                        @endif
                        @if($room->completed_at)
                            <span class="text-gray-600 dark:text-gray-400">Completed: {{ $room->completed_at->format('M d, Y') }}</span>
                        @endif
                    </div>
                    
                    @if($room->description)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Description</h3>
                            <p class="whitespace-pre-wrap">{{ $room->getTranslated('description') }}</p>
                        </div>
                    @endif
                    
                    @if($room->summary)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-2">Summary</h3>
                            <p class="whitespace-pre-wrap">{{ $room->getTranslated('summary') }}</p>
                        </div>
                    @endif
                    
                    @if($room->room_url)
                        <div class="mb-6">
                            <a href="{{ $room->room_url }}" target="_blank" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 inline-block">Visit Room</a>
                        </div>
                    @endif
                    
                    @if($room->tags->count() > 0)
                        <div class="mt-6 flex flex-wrap gap-2">
                            @foreach($room->tags as $tag)
                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-xs">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>



