<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('app.admin.blog.header') }}</h2>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('status'))
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
                <p class="text-green-800 font-medium">{{ session('status') }}</p>
            </div>
            @endif

            <div x-data="{
                activeTab: 'list',
                showCreateForm: false,
                showImportForm: false,
                editingBlog: null,
                init() {
                    // Check if we should show create form from URL hash
                    if (window.location.hash === '#create') {
                        this.activeTab = 'create';
                        this.showCreateForm = true;
                    } else if (window.location.hash === '#import') {
                        this.activeTab = 'import';
                        this.showImportForm = true;
                    }
                },
                switchTab(tab) {
                    this.activeTab = tab;
                    this.showCreateForm = tab === 'create';
                    this.showImportForm = tab === 'import';
                    window.location.hash = tab;
                },
                editBlog(blogId) {
                    window.location.href = '/admin/blogs/' + blogId + '/edit';
                }
            }">
                <!-- Tabs -->
                <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex -mb-px">
                            <button @click="switchTab('list')" :class="activeTab === 'list' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 text-sm font-medium border-b-2 transition-colors">
                                📋 All Blog Posts
                            </button>
                            <button @click="switchTab('create')" :class="activeTab === 'create' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 text-sm font-medium border-b-2 transition-colors">
                                ✏️ Create New
                            </button>
                            <button @click="switchTab('import')" :class="activeTab === 'import' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="px-6 py-4 text-sm font-medium border-b-2 transition-colors">
                                📥 Import from LinkedIn
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- List Tab -->
                <div x-show="activeTab === 'list'" class="space-y-6">
                    <!-- All Blog Posts -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold">All Blog Posts</h3>
                                <div class="text-sm text-gray-500">Total: {{ $blogs->total() }}</div>
                            </div>
                            
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                        <th class="py-3 px-2">Title</th>
                                        <th class="py-3 px-2">Category</th>
                                        <th class="py-3 px-2">Status</th>
                                        <th class="py-3 px-2">Published</th>
                                        <th class="py-3 px-2 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($blogs as $blog)
                                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="py-3 px-2">
                                                <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-blue-600 hover:underline font-medium">
                                                    {{ $blog->getTranslated('title') ?: $blog->slug }}
                                                </a>
                                            </td>
                                            <td class="py-3 px-2">{{ $blog->category ?? 'Uncategorized' }}</td>
                                            <td class="py-3 px-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $blog->is_published ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                                    {{ $blog->is_published ? 'Published' : 'Draft' }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-2">{{ $blog->published_at ? $blog->published_at->format('M d, Y') : '-' }}</td>
                                            <td class="py-3 px-2 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if($blog->linkedin_url)
                                                        <a href="{{ $blog->linkedin_url }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-200" title="View on LinkedIn">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                                            </svg>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                                                    <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-gray-500">
                                                No blog posts yet. 
                                                <button @click="switchTab('create')" class="text-blue-600 hover:underline">Create your first blog post</button>
                                                or 
                                                <button @click="switchTab('import')" class="text-blue-600 hover:underline">import from LinkedIn</button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            
                            @if($blogs->hasPages())
                                <div class="mt-4">{{ $blogs->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Create Tab -->
                <div x-show="activeTab === 'create'" class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Create New Blog Post</h3>
                            
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
                                    <label class="block text-sm mb-1">LinkedIn Post URL (optional)</label>
                                    <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" class="w-full rounded border-gray-300" placeholder="https://www.linkedin.com/feed/update/..." />
                                    <p class="text-xs text-gray-500 mt-1">Add the LinkedIn post URL if this blog is related to a LinkedIn post</p>
                                    @error('linkedin_url')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
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
                                
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="switchTab('list')" class="px-4 py-2 text-sm rounded border">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm rounded bg-blue-600 text-white">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Import Tab -->
                <div x-show="activeTab === 'import'" class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">📥 Import from LinkedIn</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Simply copy your LinkedIn post content and paste it here. This will create a blog post automatically.
                            </p>
                            
                            <form method="POST" action="{{ route('admin.linkedin.import') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <x-dual-language-input 
                                        name="title" 
                                        label="Post Title" 
                                        :value="old('title') ? (is_array(old('title')) ? old('title') : ['en' => old('title'), 'ja' => '']) : null"
                                        required="true"
                                    />
                                    @error('title')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    @error('title.en')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <x-dual-language-input 
                                        name="content" 
                                        label="Post Content" 
                                        :value="old('content') ? (is_array(old('content')) ? old('content') : ['en' => old('content'), 'ja' => '']) : null"
                                        rows="8"
                                        required="true"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">Just copy and paste your LinkedIn post text here. Type in English and it will auto-translate to Japanese.</p>
                                    @error('content')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    @error('content.en')
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
                                
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="switchTab('list')" class="px-4 py-2 text-sm rounded border">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm rounded bg-blue-600 text-white">Import as Blog Post</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
