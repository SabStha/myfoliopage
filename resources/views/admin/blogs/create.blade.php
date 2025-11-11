<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New Blog Post</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm mb-1">Title <span class="text-red-500">*</span></label>
                            <x-dual-language-input 
                                name="title" 
                                label="Title" 
                                required="true"
                            />
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Slug</label>
                            <input name="slug" value="{{ old('slug') }}" class="w-full rounded border-gray-300" placeholder="Auto-generated if left empty" />
                            @error('slug')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Excerpt</label>
                            <x-dual-language-input 
                                name="excerpt" 
                                label="Excerpt" 
                                rows="3"
                            />
                            @error('excerpt')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Content</label>
                            <x-dual-language-input 
                                name="content" 
                                label="Content" 
                                rows="10"
                            />
                            @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm mb-1">Category</label>
                                <input name="category" value="{{ old('category') }}" class="w-full rounded border-gray-300" placeholder="e.g., Laravel, Frontend" />
                                @error('category')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Published At</label>
                                <input type="datetime-local" name="published_at" value="{{ old('published_at') }}" class="w-full rounded border-gray-300" />
                                @error('published_at')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Tags (comma separated)</label>
                            <input name="tags" value="{{ old('tags') }}" class="w-full rounded border-gray-300" placeholder="Laravel, PHP, Security" />
                            <p class="text-xs text-gray-500 mt-1">Enter tags separated by commas</p>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Cover Image</label>
                            <input type="file" name="image" accept="image/*" class="w-full" />
                            <p class="text-xs text-gray-500 mt-1">Recommended: 1200x630px or similar aspect ratio</p>
                            @error('image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }} />
                                <span class="text-sm">Publish immediately</span>
                            </label>
                        </div>
                        <div class="flex justify-between items-center">
                            <a href="{{ route('admin.blogs.index') }}#linkedin" onclick="event.preventDefault(); window.location.href='{{ route('admin.blogs.index') }}'; setTimeout(() => { showTab('linkedin'); }, 100);" class="text-sm text-blue-600 hover:underline">📥 Import from LinkedIn instead</a>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.blogs.index') }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
                                <button class="px-3 py-2 text-sm rounded bg-blue-600 text-white">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

