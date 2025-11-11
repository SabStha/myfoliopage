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

            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <button onclick="showTab('blogs')" id="tab-blogs" class="tab-button py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                        {{ __('app.admin.blog.all_blog_posts') }}
                    </button>
                    <button onclick="showTab('linkedin')" id="tab-linkedin" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        {{ __('app.admin.blog.linkedin_sync') }}
                    </button>
                </nav>
            </div>

            <!-- Blog Posts Tab -->
            <div id="content-blogs" class="tab-content">
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
                                                <a href="{{ $blog->linkedin_url }}" target="_blank" class="text-blue-600 hover:underline" title="View on LinkedIn">🔗 LinkedIn</a>
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

            <!-- LinkedIn Sync Tab -->
            <div id="content-linkedin" class="tab-content hidden space-y-6">
                <!-- Import from LinkedIn -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">{{ __('app.admin.blog.import_from_linkedin') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('app.admin.blog.import_description') }}
                        </p>
                        
                        <form method="POST" action="{{ route('admin.linkedin.import') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm mb-1">{{ __('app.admin.blog.post_title') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded border-gray-300" required placeholder="{{ __('app.admin.blog.post_title_placeholder') }}" />
                                @error('title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm mb-1">{{ __('app.admin.blog.post_content') }} <span class="text-red-500">*</span></label>
                                <textarea name="content" rows="8" class="w-full rounded border-gray-300" required placeholder="{{ __('app.admin.blog.post_content_placeholder') }}">{{ old('content') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.blog.post_content_hint') }}</p>
                                @error('content')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm mb-1">{{ __('app.admin.blog.linkedin_url') }}</label>
                                    <input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" class="w-full rounded border-gray-300" placeholder="{{ __('app.admin.blog.linkedin_url_placeholder') }}" />
                                    <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.blog.linkedin_url_hint') }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm mb-1">{{ __('app.admin.blog.category_optional') }}</label>
                                    <input type="text" name="category" value="{{ old('category', 'LinkedIn Post') }}" class="w-full rounded border-gray-300" placeholder="e.g., Daily Learning" />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm mb-1">{{ __('app.admin.blog.published_date') }}</label>
                                <input type="date" name="published_at" value="{{ old('published_at', date('Y-m-d')) }}" class="w-full rounded border-gray-300" />
                            </div>
                            
                            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                                {{ __('app.admin.blog.import_as_blog_post') }}
                            </button>
                        </form>
                        
                        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                            <h4 class="font-semibold text-sm mb-2">{{ __('app.admin.blog.how_to_use') }}</h4>
                            <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                                <li>{{ __('app.admin.blog.how_to_1') }}</li>
                                <li>{{ __('app.admin.blog.how_to_2') }}</li>
                                <li>{{ __('app.admin.blog.how_to_3') }}</li>
                                <li>{{ __('app.admin.blog.how_to_4') }}</li>
                                <li>{{ __('app.admin.blog.how_to_5') }}</li>
                                <li>{{ __('app.admin.blog.how_to_6') }}</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Copy for LinkedIn -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">{{ __('app.admin.blog.copy_for_linkedin') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {{ __('app.admin.blog.copy_description') }}
                        </p>
                        
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-4">
                            <label class="block text-sm mb-2">{{ __('app.admin.blog.select_blog_post') }}</label>
                            <select id="blog-select" class="w-full rounded border-gray-300">
                                <option value="">{{ __('app.admin.blog.choose_blog_post') }}</option>
                                @foreach(\App\Models\Blog::where('is_published', true)->latest('published_at')->get() as $blog)
                                    <option value="{{ $blog->id }}" data-slug="{{ $blog->slug }}">{{ $blog->getTranslated('title') }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button id="copy-linkedin-btn" onclick="copyToLinkedIn()" disabled class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            {{ __('app.admin.blog.copy_button') }}
                        </button>
                        
                        <div id="linkedin-content" class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hidden">
                            <p class="text-sm font-semibold mb-2">{{ __('app.admin.blog.formatted_content') }}</p>
                            <pre id="linkedin-text" class="text-sm whitespace-pre-wrap"></pre>
                        </div>
                    </div>
                </div>

                <!-- Previously Imported Posts -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">{{ __('app.admin.blog.previously_imported') }}</h3>
                        
                        @php
                            $linkedinBlogs = \App\Models\Blog::whereNotNull('linkedin_url')
                                ->latest('published_at')
                                ->paginate(15);
                        @endphp
                        
                        @if($linkedinBlogs->count() > 0)
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                        <th class="py-2">{{ __('app.admin.blog.title') }}</th>
                                        <th class="py-2">{{ __('app.admin.blog.published') }}</th>
                                        <th class="py-2">{{ __('app.admin.blog.view_on_linkedin') }}</th>
                                        <th class="py-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($linkedinBlogs as $blog)
                                        <tr class="border-b border-gray-100 dark:border-gray-700">
                                            <td class="py-2">
                                                <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-blue-600 hover:underline">
                                                    {{ $blog->getTranslated('title') }}
                                                </a>
                                            </td>
                                            <td class="py-2">
                                                {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '-' }}
                                            </td>
                                            <td class="py-2">
                                                @if($blog->linkedin_url)
                                                    <a href="{{ $blog->linkedin_url }}" target="_blank" class="text-blue-600 hover:underline">
                                                        {{ __('app.admin.blog.view_on_linkedin') }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="py-2 text-right">
                                                <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-blue-600 hover:underline">{{ __('app.admin.blog.edit') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">{{ $linkedinBlogs->links() }}</div>
                        @else
                            <p class="text-gray-500">{{ __('app.admin.blog.no_linkedin_posts') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active styles from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active styles to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-500', 'text-blue-600');
        }

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
