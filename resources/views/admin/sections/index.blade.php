<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Sections for {{ $category->name }}
                    @if(isset($item))
                        <span class="text-sm font-normal text-gray-600 dark:text-gray-400">- {{ $item->title }}</span>
                    @endif
                </h2>
                @if(isset($item))
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Managing sections for item: {{ $item->title }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if(isset($item))
                    <a href="{{ route('admin.nav.links.categories.items.index', [$category->navLinksMany->first()->navItem ?? null, $category->navLinksMany->first() ?? null, $category]) }}" 
                       class="px-3 py-2 text-sm rounded border border-gray-300 hover:bg-gray-50">Back to Items</a>
                @endif
                <a href="{{ route('admin.sections.create', $category) }}{{ isset($item) ? '?item=' . $item->id : '' }}" class="px-3 py-2 text-sm rounded bg-blue-600 text-white">New Section</a>
            </div>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg">
                    <p class="text-green-800">{{ session('status') }}</p>
                </div>
            @endif
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        @if(isset($item))
                            <a href="{{ route('admin.nav.links.categories.items.index', [$category->navLinksMany->first()->navItem ?? null, $category->navLinksMany->first() ?? null, $category]) }}" 
                               class="text-blue-600 hover:underline text-sm">
                                ← Back to Items
                            </a>
                        @else
                            <a href="{{ route('admin.nav.links.categories.index', [$category->navLinksMany->first()->navItem ?? null, $category->navLinksMany->first() ?? null]) }}" 
                               class="text-blue-600 hover:underline text-sm">
                                ← Back to Categories
                            </a>
                        @endif
                    </div>
                    
                    @if($sections->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500">No sections yet for this category.</p>
                            <a href="{{ route('admin.sections.create', $category) }}{{ isset($item) ? '?item=' . $item->id : '' }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Create First Section
                            </a>
                        </div>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2">Name</th>
                                    <th class="py-2">Slug</th>
                                    <th class="py-2">Position</th>
                                    <th class="py-2">Description</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sections as $section)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-2">{{ $section->name }}</td>
                                        <td class="py-2 font-mono text-xs">{{ $section->slug }}</td>
                                        <td class="py-2">{{ $section->position }}</td>
                                        <td class="py-2">{{ Str::limit($section->description ?? '—', 50) }}</td>
                                            <td class="py-2 text-right space-x-2">
                                                <a href="{{ route('admin.sections.edit', [$category, $section]) }}{{ isset($item) ? '?item=' . $item->id : '' }}" class="text-blue-600 hover:underline">Edit</a>
                                                <form method="POST" action="{{ route('admin.sections.destroy', [$category, $section]) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="redirect_to" value="{{ isset($item) ? route('admin.nav.links.categories.items.index', [$category->navLinksMany->first()->navItem ?? null, $category->navLinksMany->first() ?? null, $category]) : '' }}">
                                                    <button class="text-red-600 hover:underline" onclick="return confirm('Delete this section?')">Delete</button>
                                                </form>
                                            </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

