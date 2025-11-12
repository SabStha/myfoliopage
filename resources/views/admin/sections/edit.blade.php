<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Section: {{ $section->name }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.sections.update', [$category, $section]) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm mb-1">Name</label>
                            <input name="name" class="w-full rounded border-gray-300" value="{{ old('name', $section->name) }}" required />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Slug</label>
                            <input name="slug" class="w-full rounded border-gray-300" value="{{ old('slug', $section->slug) }}" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Description</label>
                            <textarea name="description" rows="3" class="w-full rounded border-gray-300">{{ old('description', $section->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Position</label>
                            <input type="number" name="position" class="w-full rounded border-gray-300" value="{{ old('position', $section->position) }}" />
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.sections.index', $category) }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
                            <button class="px-3 py-2 text-sm rounded bg-blue-600 text-white">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>











