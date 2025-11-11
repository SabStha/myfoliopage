<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Create Section for {{ $category->name }}
                    @if(isset($item))
                        <span class="text-sm font-normal text-gray-600 dark:text-gray-400">- {{ $item->title }}</span>
                    @endif
                </h2>
                @if(isset($item))
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Creating section for item: {{ $item->title }}</p>
                @endif
            </div>
            @if(isset($item))
                <a href="{{ route('admin.nav.links.categories.items.index', [$category->navLinksMany->first()->navItem ?? null, $category->navLinksMany->first() ?? null, $category]) }}" 
                   class="px-3 py-2 text-sm rounded border border-gray-300 hover:bg-gray-50">Cancel</a>
            @endif
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.sections.store', $category) }}" class="space-y-4">
                        @csrf
                        @if(isset($item))
                            <input type="hidden" name="item" value="{{ $item->id }}">
                        @endif
                        <div>
                            <label class="block text-sm mb-1">Name</label>
                            <input name="name" class="w-full rounded border-gray-300" value="{{ old('name') }}" required />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Slug</label>
                            <input name="slug" class="w-full rounded border-gray-300" value="{{ old('slug') }}" placeholder="Auto-generated if empty" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Description</label>
                            <textarea name="description" rows="3" class="w-full rounded border-gray-300">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Position</label>
                            <input type="number" name="position" class="w-full rounded border-gray-300" value="{{ old('position', 0) }}" />
                        </div>
                        <div class="flex justify-end gap-2">
                            @if(isset($item))
                                <a href="{{ route('admin.nav.links.categories.items.index', [$category->navLinksMany->first()->navItem ?? null, $category->navLinksMany->first() ?? null, $category]) }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
                            @else
                                <a href="{{ route('admin.sections.index', $category) }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
                            @endif
                            <button class="px-3 py-2 text-sm rounded bg-blue-600 text-white">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

