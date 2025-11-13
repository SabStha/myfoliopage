<x-modal name="blog-detail-modal" maxWidth="4xl">
    <div class="max-h-[90vh] overflow-y-auto" x-data="{
        blog: null,
        loading: true,
        slug: null,
        init() {
            window.addEventListener('load-blog-modal', (e) => {
                this.slug = e.detail.slug;
                this.loadBlog();
            });
        },
        async loadBlog() {
            if (!this.slug) return;
            this.loading = true;
            try {
                const response = await fetch(`/api/blogs/${this.slug}`);
                if (response.ok) {
                    this.blog = await response.json();
                } else {
                    console.error('Blog not found');
                    this.$dispatch('close-modal', 'blog-detail-modal');
                }
            } catch (error) {
                console.error('Error loading blog:', error);
                this.$dispatch('close-modal', 'blog-detail-modal');
            } finally {
                this.loading = false;
            }
        }
    }">
        <!-- Loading State -->
        <div x-show="loading" class="p-16 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">Loading blog post...</p>
        </div>

        <!-- Blog Content -->
        <div x-show="!loading && blog" class="bg-white dark:bg-gray-800">
            <!-- Header Section with Close Button -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span x-show="blog && blog.category" class="px-3 py-1 rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-medium text-xs uppercase tracking-wider" x-text="blog?.category || ''"></span>
                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="blog?.published_at || ''"></span>
                </div>
                <button 
                    @click="$dispatch('close-modal', 'blog-detail-modal')" 
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    aria-label="Close"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content Area -->
            <div class="px-6 py-8">
                <!-- Title -->
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-gray-100 mb-6 leading-tight tracking-tight" x-text="blog?.title || ''"></h1>

                <!-- Featured Image -->
                <div x-show="blog && blog.imageUrl" class="mb-8 -mx-6">
                    <div class="w-full h-64 md:h-96 lg:h-[500px] overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                        <img 
                            :src="blog?.imageUrl || ''" 
                            :alt="blog?.title || ''"
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>

                <!-- Tags -->
                <div x-show="blog && blog.tags && blog.tags.length > 0" class="flex flex-wrap gap-2 mb-8">
                    <template x-for="tag in (blog?.tags || [])" :key="tag.id">
                        <span class="px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium" x-text="tag.name"></span>
                    </template>
                </div>

                <!-- Blog Content -->
                <div class="prose prose-lg dark:prose-invert max-w-none mb-8">
                    <template x-if="blog && blog.content">
                        <div 
                            class="text-gray-800 dark:text-gray-200 leading-relaxed text-base md:text-lg" 
                            x-html="blog.content.replace(/\n/g, '<br>')"
                        ></div>
                    </template>
                    <template x-if="!blog || !blog.content">
                        <p class="text-gray-500 italic">No content available.</p>
                    </template>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-8"></div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between">
                    <a 
                        x-show="blog && blog.linkedin_url"
                        :href="blog?.linkedin_url || '#'" 
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-all duration-200 text-sm font-semibold shadow-sm hover:shadow-md"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        View on LinkedIn
                    </a>
                    
                    <button 
                        @click="$dispatch('close-modal', 'blog-detail-modal')" 
                        class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 text-sm font-medium"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && !blog" class="p-16 text-center">
            <p class="text-gray-500 dark:text-gray-400">Blog post not found.</p>
        </div>
    </div>
</x-modal>
