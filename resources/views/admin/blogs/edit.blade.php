<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Blog Post</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.blogs.update', $blog) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-sm mb-1">Title <span class="text-red-500">*</span></label>
                            <x-dual-language-input 
                                name="title" 
                                label="Title" 
                                :value="$blog->getTranslations('title')"
                                required="true"
                            />
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Slug <span class="text-red-500">*</span></label>
                            <input name="slug" value="{{ old('slug', $blog->slug) }}" class="w-full rounded border-gray-300" required />
                            @error('slug')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Excerpt</label>
                            <x-dual-language-input 
                                name="excerpt" 
                                label="Excerpt" 
                                :value="$blog->getTranslations('excerpt')"
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
                                :value="$blog->getTranslations('content')"
                                rows="10"
                            />
                            @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm mb-1">Category</label>
                                <input name="category" value="{{ old('category', $blog->category) }}" class="w-full rounded border-gray-300" placeholder="e.g., Laravel, Frontend" />
                                @error('category')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Published At</label>
                                <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($blog->published_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded border-gray-300" />
                                @error('published_at')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">LinkedIn Post URL (optional)</label>
                            <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $blog->linkedin_url) }}" class="w-full rounded border-gray-300" placeholder="https://www.linkedin.com/feed/update/..." />
                            <p class="text-xs text-gray-500 mt-1">Add the LinkedIn post URL if this blog is related to a LinkedIn post</p>
                            @if($blog->linkedin_url)
                                <p class="text-xs text-blue-600 mt-1">
                                    <a href="{{ $blog->linkedin_url }}" target="_blank" class="hover:underline">🔗 View LinkedIn Post</a>
                                </p>
                            @endif
                            @error('linkedin_url')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Tags (comma separated)</label>
                            <input name="tags" value="{{ old('tags', $blogTags) }}" class="w-full rounded border-gray-300" placeholder="Laravel, PHP, Security" />
                            <p class="text-xs text-gray-500 mt-1">Enter tags separated by commas</p>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Cover Image</label>
                            @if($blog->media->where('type', 'image')->first())
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $blog->media->where('type', 'image')->first()->path) }}" alt="Current cover" class="w-32 h-32 object-cover rounded border" />
                                    <p class="text-xs text-gray-500 mt-1">Current cover image</p>
                                </div>
                            @endif
                            <input type="file" name="image" accept="image/*" class="w-full" />
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image. Upload new to replace.</p>
                            @error('image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $blog->is_published) ? 'checked' : '' }} />
                                <span class="text-sm">Publish</span>
                            </label>
                        </div>
                        <div class="flex justify-between items-center">
                            <a href="{{ route('admin.linkedin.index') }}" class="text-sm text-blue-600 hover:underline" id="copy-linkedin-link" onclick="copyBlogToLinkedIn(event, {{ $blog->id }})">📋 Copy for LinkedIn</a>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.blogs.index') }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
                                <button class="px-3 py-2 text-sm rounded bg-blue-600 text-white">Save</button>
                            </div>
                        </div>
                        
                        <script>
                            async function copyBlogToLinkedIn(e, blogId) {
                                e.preventDefault();
                                try {
                                    const response = await fetch(`/admin/linkedin/${blogId}/format`);
                                    const data = await response.json();
                                    await navigator.clipboard.writeText(data.content);
                                    const link = document.getElementById('copy-linkedin-link');
                                    const originalText = link.textContent;
                                    link.textContent = '✅ Copied!';
                                    setTimeout(() => {
                                        link.textContent = originalText;
                                    }, 2000);
                                } catch (error) {
                                    alert('Failed to copy. Please try again.');
                                }
                            }
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

