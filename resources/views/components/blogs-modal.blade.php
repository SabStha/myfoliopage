<x-modal name="blogs-modal" maxWidth="6xl">
    <div class="p-6" x-data="{
        blogs: [],
        filteredBlogs: [],
        searchTerm: '',
        selectedCategory: '',
        categories: [],
        loading: true,
        init() {
            this.loadBlogs();
        },
        async loadBlogs() {
            this.loading = true;
            try {
                const response = await fetch('/api/blogs');
                this.blogs = await response.json();
                this.filteredBlogs = this.blogs;
                this.categories = [...new Set(this.blogs.map(b => b.category).filter(Boolean))];
            } catch (error) {
                console.error('Error loading blogs:', error);
            } finally {
                this.loading = false;
            }
        },
        filterBlogs() {
            this.filteredBlogs = this.blogs.filter(blog => {
                const matchesSearch = !this.searchTerm || 
                    blog.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    (blog.excerpt && blog.excerpt.toLowerCase().includes(this.searchTerm.toLowerCase())) ||
                    (blog.content && blog.content.toLowerCase().includes(this.searchTerm.toLowerCase()));
                const matchesCategory = !this.selectedCategory || blog.category === this.selectedCategory;
                return matchesSearch && matchesCategory;
            });
        },
        openBlogModal(slug) {
            window.dispatchEvent(new CustomEvent('load-blog-modal', { detail: { slug } }));
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'blogs-modal' }));
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'blog-detail-modal' }));
        }
    }" x-init="init()">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">All Blog Posts</h2>
            <button @click="$dispatch('close-modal', 'blogs-modal')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="mb-6 space-y-4">
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchTerm"
                    @input="filterBlogs()"
                    placeholder="Search blogs by title, excerpt, or content..."
                    class="w-full px-4 py-3 pl-10 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <svg class="absolute left-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            
            <div class="flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category:</label>
                <select 
                    x-model="selectedCategory"
                    @change="filterBlogs()"
                    class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">All Categories</option>
                    <template x-for="category in categories" :key="category">
                        <option :value="category" x-text="category"></option>
                    </template>
                </select>
                
                <div class="ml-auto text-sm text-gray-600 dark:text-gray-400">
                    <span x-text="filteredBlogs.length"></span> of <span x-text="blogs.length"></span> posts
                </div>
            </div>
        </div>

        <div x-show="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">Loading blogs...</p>
        </div>

        <div x-show="!loading" class="overflow-y-auto max-h-[60vh]">
            <div x-show="filteredBlogs.length === 0" class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">No blogs found matching your search.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="filteredBlogs.length > 0">
                <template x-for="blog in filteredBlogs" :key="blog.id">
                    <div 
                        @click="openBlogModal(blog.slug)"
                        class="block bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 group cursor-pointer"
                    >
                        <div class="w-full h-48 overflow-hidden bg-gray-100 dark:bg-gray-700">
                            <img 
                                :src="blog.imageUrl || '/storage/certficates/certificate-1.jpg'"
                                :alt="blog.title"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                x-on:error="$el.style.display='none'; $el.nextElementSibling.style.display='flex';"
                            />
                            <div class="w-full h-full hidden items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide" x-text="blog.category"></span>
                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="blog.published_at"></span>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200 line-clamp-2" x-text="blog.title"></h3>
                            
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed line-clamp-3" x-text="blog.excerpt"></p>
                            
                            <div class="flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 mt-3 group-hover:underline">
                                <span>{{ __('app.blog.read_more') }}</span>
                                <svg class="w-4 h-4 ml-2 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-modal>
