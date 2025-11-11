<x-modal name="section-content-modal" maxWidth="6xl">
    <div class="p-0" x-data="sectionContentModalData()">
        <!-- Loading State -->
        <div x-show="loading" class="text-center py-20">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400 font-medium">Loading section content...</p>
        </div>

        <!-- Content -->
        <div x-show="!loading && data" class="overflow-hidden">
            <!-- Hero Header -->
            <div class="relative bg-gradient-to-br from-teal-600 via-blue-600 to-indigo-600 px-8 py-10 text-white">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
                </div>
                
                <button 
                    @click="$dispatch('close-modal', 'section-content-modal')" 
                    class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 backdrop-blur-sm transition-all duration-200 flex items-center justify-center group"
                >
                    <svg class="w-6 h-6 text-white group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <div class="relative z-10">
                    <h1 class="text-4xl font-bold mb-4 leading-tight" x-text="data?.section?.title || 'Section Content'"></h1>
                    <p class="text-teal-100 text-lg" x-show="data?.section?.summary" x-text="data?.section?.summary"></p>
                </div>
            </div>

            <!-- Content Items -->
            <div class="px-8 py-8 max-h-[70vh] overflow-y-auto space-y-8">
                <!-- Book Pages -->
                <div x-show="data && data.bookPages && data.bookPages.length > 0">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Book Pages</h2>
                        <span class="text-sm text-gray-500" x-text="`(${data?.bookPages?.length || 0})`"></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="item in (data?.bookPages || [])" :key="item.id">
                            <div 
                                @click="openItemModal(item)"
                                class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all cursor-pointer bg-white"
                            >
                                {{-- Badges --}}
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <template x-if="item.status === 'completed'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            ✓ Completed
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'in_progress'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            ⏳ In Progress
                                        </span>
                                    </template>
                                    <template x-if="item.difficulty">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-blue-100 text-blue-800': item.difficulty === 'Beginner',
                                                'bg-orange-100 text-orange-800': item.difficulty === 'Intermediate',
                                                'bg-red-100 text-red-800': item.difficulty === 'Advanced'
                                            }"
                                            x-text="item.difficulty">
                                        </span>
                                    </template>
                                    <template x-if="item.time_spent">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                            ⏱ <span x-text="item.time_spent"></span> min
                                        </span>
                                    </template>
                                </div>
                                <h3 class="font-semibold text-gray-900 mb-2" x-text="item.title"></h3>
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3" x-show="item.summary" x-text="item.summary"></p>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span x-show="item.book_title" x-text="item.book_title"></span>
                                    <span x-show="item.author" x-text="`by ${item.author}`"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Code Summaries -->
                <div x-show="data && data.codeSummaries && data.codeSummaries.length > 0">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Code Summaries</h2>
                        <span class="text-sm text-gray-500" x-text="`(${data?.codeSummaries?.length || 0})`"></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="item in (data?.codeSummaries || [])" :key="item.id">
                            <div 
                                @click="openItemModal(item)"
                                class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all cursor-pointer bg-white"
                            >
                                <h3 class="font-semibold text-gray-900 mb-2" x-text="item.title"></h3>
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3" x-show="item.summary" x-text="item.summary"></p>
                                <span class="text-xs text-gray-500" x-show="item.language" x-text="item.language"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Rooms -->
                <div x-show="data && data.rooms && data.rooms.length > 0">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Rooms</h2>
                        <span class="text-sm text-gray-500" x-text="`(${data?.rooms?.length || 0})`"></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="item in (data?.rooms || [])" :key="item.id">
                            <div 
                                @click="openItemModal(item)"
                                class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all cursor-pointer bg-white"
                            >
                                {{-- Badges --}}
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <template x-if="item.status === 'completed'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            ✓ Completed
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'in_progress'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            ⏳ In Progress
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'retired'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                            Retired
                                        </span>
                                    </template>
                                    <template x-if="item.difficulty">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-800': item.difficulty === 'Easy',
                                                'bg-yellow-100 text-yellow-800': item.difficulty === 'Medium',
                                                'bg-orange-100 text-orange-800': item.difficulty === 'Hard',
                                                'bg-red-100 text-red-800': item.difficulty === 'Insane'
                                            }"
                                            x-text="item.difficulty">
                                        </span>
                                    </template>
                                    <template x-if="item.time_spent">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                            ⏱ <span x-text="item.time_spent"></span> min
                                        </span>
                                    </template>
                                    <template x-if="item.score_points_earned">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            🏆 <span x-text="item.score_points_earned"></span> pts
                                        </span>
                                    </template>
                                </div>
                                <h3 class="font-semibold text-gray-900 mb-2" x-text="item.title"></h3>
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3" x-show="item.summary" x-text="item.summary"></p>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span x-show="item.platform" x-text="item.platform"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Certificates -->
                <div x-show="data && data.certificates && data.certificates.length > 0">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-yellow-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Certificates</h2>
                        <span class="text-sm text-gray-500" x-text="`(${data?.certificates?.length || 0})`"></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="item in (data?.certificates || [])" :key="item.id">
                            <div 
                                @click="openItemModal(item)"
                                class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all cursor-pointer bg-white"
                            >
                                <h3 class="font-semibold text-gray-900 mb-2" x-text="item.title"></h3>
                                <p class="text-sm text-gray-600 mb-3" x-show="item.provider" x-text="item.provider"></p>
                                <span class="text-xs text-gray-500" x-show="item.issued_at" x-text="formatDate(item.issued_at)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Courses -->
                <div x-show="data && data.courses && data.courses.length > 0">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-cyan-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Courses</h2>
                        <span class="text-sm text-gray-500" x-text="`(${data?.courses?.length || 0})`"></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="item in (data?.courses || [])" :key="item.id">
                            <div 
                                @click="openItemModal(item)"
                                class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all cursor-pointer bg-white"
                            >
                                <h3 class="font-semibold text-gray-900 mb-2" x-text="item.title"></h3>
                                <p class="text-sm text-gray-600 mb-3" x-show="item.provider" x-text="item.provider"></p>
                                <span class="text-xs text-gray-500" x-show="item.completed_at" x-text="`Completed: ${formatDate(item.completed_at)}`"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="data && (!data.bookPages || data.bookPages.length === 0) && (!data.codeSummaries || data.codeSummaries.length === 0) && (!data.rooms || data.rooms.length === 0) && (!data.certificates || data.certificates.length === 0) && (!data.courses || data.courses.length === 0)" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-gray-600 font-medium">No content items in this section yet.</p>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div x-show="!loading && error" class="text-center py-20 px-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 mb-4">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Error Loading Content</h3>
            <p class="text-red-600 dark:text-red-400" x-text="error"></p>
        </div>
    </div>

    <script>
        function sectionContentModalData() {
            return {
                loading: false,
                data: null,
                error: null,
                init() {
                    // Listen for load-section-content-modal event
                    window.addEventListener('load-section-content-modal', (event) => {
                        const { sectionId } = event.detail;
                        if (sectionId) {
                            this.loadSectionContent(sectionId);
                        }
                    });
                },
                async loadSectionContent(sectionId) {
                    this.loading = true;
                    this.error = null;
                    this.data = null;

                    try {
                        const url = `/api/sections/${sectionId}/content`;
                        console.log('Loading section content from:', url);
                        
                        const response = await fetch(url);
                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Failed to load section content:', response.status, errorText);
                            throw new Error(`Failed to load content: ${response.status} ${response.statusText}`);
                        }
                        
                        this.data = await response.json();
                        console.log('Section content loaded successfully:', this.data);
                    } catch (e) {
                        console.error('Error loading section content:', e);
                        this.error = e.message || 'Failed to load section content';
                        this.data = null;
                    } finally {
                        this.loading = false;
                    }
                },
                openItemModal(item) {
                    // Close section modal first
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'section-content-modal' }));
                    
                    // Open individual item modal
                    if (window.openContentModal) {
                        window.openContentModal(item.type, item.id, item.slug);
                    }
                },
                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                }
            }
        }
    </script>
</x-modal>


