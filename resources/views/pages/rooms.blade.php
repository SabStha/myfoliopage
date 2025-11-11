<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Rooms') }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($categories->count() > 0)
                        <div class="mb-6 flex flex-wrap gap-2">
                            @foreach($categories as $category)
                                <a href="#category-{{ $category->id }}" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-700 text-sm hover:bg-gray-200 dark:hover:bg-gray-600">{{ $category->getTranslated('name') }}</a>
                            @endforeach
                        </div>
                    @endif
                    
                    @if($rooms->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($rooms as $room)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold mb-2">
                                        <a href="{{ route('rooms.show', $room) }}" class="hover:underline">{{ $room->getTranslated('title') }}</a>
                                    </h3>
                                    <div class="flex gap-2 mb-2">
                                        @if($room->platform)
                                            <span class="px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900 text-xs">{{ $room->platform }}</span>
                                        @endif
                                        @if($room->difficulty)
                                            <span class="px-2 py-0.5 rounded bg-orange-100 dark:bg-orange-900 text-xs">{{ $room->difficulty }}</span>
                                        @endif
                                    </div>
                                    @if($room->summary)
                                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 line-clamp-3">{{ $room->getTranslated('summary') }}</p>
                                    @endif
                                    @if($room->categories->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            @foreach($room->categories as $category)
                                                <span class="px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-xs">{{ $category->getTranslated('name') }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($room->completed_at)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Completed: {{ $room->completed_at->format('M d, Y') }}</div>
                                    @endif
                                    @if($room->room_url)
                                        <a href="{{ $room->room_url }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-2 inline-block">Visit Room</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $rooms->links() }}</div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No rooms yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>



