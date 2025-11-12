<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Lab</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.labs.update', $lab) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm mb-1">Title</label>
                            <input name="title" class="w-full rounded border-gray-300" value="{{ old('title', $lab->title) }}" required />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Slug</label>
                            <input name="slug" class="w-full rounded border-gray-300" value="{{ old('slug', $lab->slug) }}" required />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm mb-1">Platform</label>
                                <input name="platform" class="w-full rounded border-gray-300" value="{{ old('platform', $lab->platform) }}" />
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Completed At</label>
                                <input type="date" name="completed_at" class="w-full rounded border-gray-300" value="{{ old('completed_at', optional($lab->completed_at)->format('Y-m-d')) }}" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Room URL</label>
                            <input name="room_url" class="w-full rounded border-gray-300" value="{{ old('room_url', $lab->room_url) }}" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Summary</label>
                            <textarea name="summary" class="w-full rounded border-gray-300">{{ old('summary', $lab->summary) }}</textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.labs.index') }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
                            <button class="px-3 py-2 text-sm rounded bg-blue-600 text-white">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>















