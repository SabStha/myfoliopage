<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('app.admin.blog.header') }}</h2>
            <a href="{{ route('admin.blogs.create') }}" class="px-3 py-2 text-sm rounded bg-blue-600 text-white">{{ __('app.admin.blog.new_blog_post') }}</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('status'))
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
                <p class="text-green-800 font-medium">{{ session('status') }}</p>
            </div>
            @endif

            <!-- Import from LinkedIn Section -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">📥 Import from LinkedIn</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Copy your LinkedIn post content and paste it here to create a blog post.
                    </p>
                    
                    <form method="POST" action="{{ route('admin.linkedin.import') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm mb-1">Post Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded border-gray-300" required placeholder="e.g., My Daily Learning Journey" />
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm mb-1">Post Content <span class="text-red-500">*</span></label>
                            <textarea name="content" rows="6" class="w-full rounded border-gray-300" required placeholder="Paste your LinkedIn post content here...">{{ old('content') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Just copy and paste your LinkedIn post text here</p>
                            @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm mb-1">LinkedIn Post URL (optional)</label>
                                <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" class="w-full rounded border-gray-300" placeholder="https://www.linkedin.com/feed/update/..." />
                                <p class="text-xs text-gray-500 mt-1">Copy the URL from your LinkedIn post</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm mb-1">Category (optional)</label>
                                <input type="text" name="category" value="{{ old('category', 'LinkedIn Post') }}" class="w-full rounded border-gray-300" placeholder="e.g., Daily Learning" />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm mb-1">Published Date</label>
                            <input type="date" name="published_at" value="{{ old('published_at', date('Y-m-d')) }}" class="w-full rounded border-gray-300" />
                        </div>
                        
                        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                            Import as Blog Post
                        </button>
                    </form>
                </div>
            </div>

            <!-- Copy for LinkedIn Section -->
            <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">📤 Copy for LinkedIn</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Select a blog post below and click "Copy for LinkedIn" to get formatted text ready to paste.
                    </p>
                    
                    <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-4">
                        <label class="block text-sm mb-2">Select a blog post:</label>
                        <select id="blog-select" class="w-full rounded border-gray-300">
                            <option value="">Choose a blog post...</option>
                            @foreach(\App\Models\Blog::where('is_published', true)->where('user_id', Auth::id())->latest('published_at')->get() as $blog)
                                <option value="{{ $blog->id }}" data-slug="{{ $blog->slug }}">{{ $blog->getTranslated('title') }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button id="copy-linkedin-btn" onclick="copyToLinkedIn()" disabled class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        📋 Copy for LinkedIn
                    </button>
                    
                    <div id="linkedin-content" class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hidden">
                        <p class="text-sm font-semibold mb-2">Formatted content (copied to clipboard):</p>
                        <pre id="linkedin-text" class="text-sm whitespace-pre-wrap"></pre>
                    </div>
                </div>
            </div>

            <!-- All Blog Posts -->
            <div class="mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2">{{ __('app.admin.blog.title') }}</th>
                                    <th class="py-2">{{ __('app.admin.blog.category') }}</th>
                                    <th class="py-2">{{ __('app.admin.blog.published') }}</th>
                                    <th class="py-2">{{ __('app.admin.blog.published_at') }}</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blogs as $blog)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-2">{{ $blog->getTranslated('title') }}</td>
                                        <td class="py-2">{{ $blog->category ?? __('app.admin.blog.uncategorized') }}</td>
                                        <td class="py-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $blog->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $blog->is_published ? __('app.admin.blog.yes') : __('app.admin.blog.no') }}
                                            </span>
                                        </td>
                                        <td class="py-2">{{ $blog->published_at ? $blog->published_at->format('M d, Y') : '-' }}</td>
                                        <td class="py-2 text-right space-x-2">
                                            @if($blog->linkedin_url)
                                                <a href="{{ $blog->linkedin_url }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100" title="View on LinkedIn">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                                    </svg>
                                                    View LinkedIn
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-blue-600 hover:underline">{{ __('app.admin.blog.edit') }}</a>
                                            <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:underline" onclick="return confirm('{{ __('app.admin.blog.delete_confirm') }}')">{{ __('app.admin.blog.delete') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="py-4" colspan="5">{{ __('app.admin.blog.no_posts') }} <a href="{{ route('admin.blogs.create') }}" class="text-blue-600 hover:underline">{{ __('app.admin.blog.create_one') }}</a></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-4">{{ $blogs->links() }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // LinkedIn copy functionality
        const blogSelect = document.getElementById('blog-select');
        const copyBtn = document.getElementById('copy-linkedin-btn');
        const contentDiv = document.getElementById('linkedin-content');
        const contentText = document.getElementById('linkedin-text');
        
        if (blogSelect) {
            blogSelect.addEventListener('change', function() {
                copyBtn.disabled = !this.value;
            });
        }
        
        async function copyToLinkedIn() {
            const blogId = blogSelect.value;
            if (!blogId) return;
            
            try {
                const response = await fetch(`/admin/linkedin/${blogId}/format`);
                const data = await response.json();
                
                // Copy to clipboard
                await navigator.clipboard.writeText(data.content);
                
                // Show the content
                contentText.textContent = data.content;
                contentDiv.classList.remove('hidden');
                
                // Show success message
                    copyBtn.textContent = '{{ __('app.admin.blog.copied') }}';
                    setTimeout(() => {
                        copyBtn.textContent = '{{ __('app.admin.blog.copy_button') }}';
                    }, 2000);
                
            } catch (error) {
                alert('{{ __('app.admin.blog.copy_failed') }}');
                console.error(error);
            }
        }
    </script>
</x-app-layout>
