<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">LinkedIn Integration</h2>
            <a href="{{ route('admin.blogs.index') }}" class="px-3 py-2 text-sm rounded border">Back to Blogs</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('status'))
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
                <p class="text-green-800 font-medium">{{ session('status') }}</p>
            </div>
            @endif

            <!-- Import from LinkedIn -->
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
                                :value="old('title') ? (is_array(old('title')) ? old('title') : ['en' => old('title'), 'ja' => '']) : null)"
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
                        
                        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                            Import as Blog Post
                        </button>
                    </form>
                    
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-semibold text-sm mb-2">💡 How to use:</h4>
                        <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                            <li>Go to your LinkedIn post</li>
                            <li>Copy the entire post text (title and content)</li>
                            <li>Paste it into the "Post Content" field above</li>
                            <li>Add a title (or it will be auto-generated)</li>
                            <li>Optionally add the LinkedIn post URL</li>
                            <li>Click "Import as Blog Post"</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Copy for LinkedIn -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">📤 Copy for LinkedIn</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Want to post your blog to LinkedIn? Select a blog post below and click "Copy for LinkedIn" to get formatted text ready to paste.
                    </p>
                    
                    <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-4">
                        <label class="block text-sm mb-2">Select a blog post:</label>
                        <select id="blog-select" class="w-full rounded border-gray-300">
                            <option value="">Choose a blog post...</option>
                            @foreach(\App\Models\Blog::where('is_published', true)->latest('published_at')->get() as $blog)
                                <option value="{{ $blog->id }}" data-slug="{{ $blog->slug }}">{{ $blog->getTranslated('title') ?: $blog->slug }}</option>
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

            <!-- Previously Imported Posts -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Previously Imported from LinkedIn</h3>
                    
                    @if($blogs->count() > 0)
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2">Title</th>
                                    <th class="py-2">Published</th>
                                    <th class="py-2">LinkedIn URL</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($blogs as $blog)
                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-2">
                                            <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-blue-600 hover:underline">
                                                {{ $blog->getTranslated('title') ?: $blog->slug }}
                                            </a>
                                        </td>
                                        <td class="py-2">
                                            {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '-' }}
                                        </td>
                                        <td class="py-2">
                                            @if($blog->linkedin_url)
                                                <a href="{{ $blog->linkedin_url }}" target="_blank" class="text-blue-600 hover:underline">
                                                    View on LinkedIn
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2 text-right">
                                            <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-blue-600 hover:underline">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">{{ $blogs->links() }}</div>
                    @else
                        <p class="text-gray-500">No LinkedIn posts imported yet. Use the import form above to get started.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
        const blogSelect = document.getElementById('blog-select');
        const copyBtn = document.getElementById('copy-linkedin-btn');
        const contentDiv = document.getElementById('linkedin-content');
        const contentText = document.getElementById('linkedin-text');
        
        blogSelect.addEventListener('change', function() {
            copyBtn.disabled = !this.value;
        });
        
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
                copyBtn.textContent = '✅ Copied!';
                setTimeout(() => {
                    copyBtn.textContent = '📋 Copy for LinkedIn';
                }, 2000);
                
            } catch (error) {
                alert('Failed to copy content. Please try again.');
                console.error(error);
            }
        }
    </script>
</x-app-layout>
