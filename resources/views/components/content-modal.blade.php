<x-modal name="content-modal" maxWidth="4xl">
    <div class="p-0" x-data="contentModalData()">
        <!-- Loading State -->
        <div x-show="loading" class="text-center py-20">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-200 border-t-blue-600"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400 font-medium">Loading content...</p>
        </div>

        <!-- Content -->
        <div x-show="!loading && data" class="overflow-hidden">
            <!-- Hero Header with Gradient Background -->
            <div class="relative bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 px-8 py-10 text-white">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
                </div>
                
                <!-- Close Button -->
                <button 
                    @click="$dispatch('close-modal', 'content-modal')" 
                    class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 backdrop-blur-sm transition-all duration-200 flex items-center justify-center group"
                >
                    <svg class="w-6 h-6 text-white group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Title and Categories -->
                <div class="relative z-10">
                    <h1 class="text-4xl font-bold mb-4 leading-tight" x-text="data?.title || 'Loading...'"></h1>
                    
                    <!-- Categories -->
                    <div class="flex flex-wrap gap-2 mt-4" x-show="data && data.categories && Array.isArray(data.categories) && data.categories.length > 0">
                        <template x-for="category in (data?.categories || [])" :key="category?.id">
                            <span class="px-3 py-1.5 text-sm font-medium rounded-full bg-white/20 backdrop-blur-sm border border-white/30 hover:bg-white/30 transition-all duration-200" x-text="category?.name"></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Image Section -->
            <div x-show="data && data.imageUrl" class="relative -mt-8 px-8">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border-4 border-white dark:border-gray-800">
                    <img 
                        :src="data?.imageUrl" 
                        :alt="data?.title || 'Image'" 
                        class="w-full h-80 object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                </div>
            </div>

            <!-- Content Body -->
            <div class="px-8 py-8 space-y-6">
                <!-- Book Page Metadata -->
                <div x-show="data && data.type === 'book-page'">
                    {{-- Badges Row --}}
                    <div class="flex flex-wrap gap-3 mb-4">
                        <template x-if="data?.status === 'completed'">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Completed
                            </span>
                        </template>
                        <template x-if="data?.status === 'in_progress'">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                In Progress
                            </span>
                        </template>
                        <template x-if="data?.difficulty">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold"
                                :class="{
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': data?.difficulty === 'Beginner',
                                    'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300': data?.difficulty === 'Intermediate',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': data?.difficulty === 'Advanced'
                                }"
                                x-text="data?.difficulty">
                            </span>
                        </template>
                        <template x-if="data?.time_spent">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="data?.time_spent"></span> min
                            </span>
                        </template>
                    </div>

                    {{-- Book Details Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div x-show="data?.book_title" class="flex items-start gap-3 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                            <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Book</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="data?.book_title"></div>
                            </div>
                        </div>
                        
                        <div x-show="data?.author" class="flex items-start gap-3 p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-100 dark:border-purple-800">
                            <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wide mb-1">Author</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="data?.author"></div>
                            </div>
                        </div>
                        
                        <div x-show="data?.page_number" class="flex items-start gap-3 p-4 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border border-amber-100 dark:border-amber-800">
                            <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-1">Page</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="data?.page_number"></div>
                            </div>
                        </div>
                        
                        <div x-show="data?.read_at" class="flex items-start gap-3 p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-100 dark:border-green-800">
                            <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">Read</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="formatDate(data?.read_at)"></div>
                            </div>
                        </div>
                    </div>

                    {{-- References --}}
                    <div x-show="data?.references" class="mt-4 p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-1">References</div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="data?.references"></div>
                    </div>
                </div>

                <!-- Course/Certificate Metadata -->
                <div x-show="data && (data.type === 'course' || data.type === 'certificate')" class="grid grid-cols-2 gap-4">
                    <div x-show="data && data.provider" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                        <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase mb-1">Provider</div>
                        <div class="text-sm font-medium" x-text="data?.provider"></div>
                    </div>
                    <div x-show="data && data.issued_at" class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                        <div class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase mb-1">Issued</div>
                        <div class="text-sm font-medium" x-text="formatDate(data?.issued_at)"></div>
                    </div>
                    <div x-show="data && data.credential_id" class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-100 dark:border-purple-800">
                        <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase mb-1">Credential ID</div>
                        <div class="text-sm font-medium" x-text="data?.credential_id"></div>
                    </div>
                    <div x-show="data && data.completed_at && data.type === 'course'" class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800">
                        <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase mb-1">Completed</div>
                        <div class="text-sm font-medium" x-text="formatDate(data?.completed_at)"></div>
                    </div>
                </div>

                <!-- Code Summary Metadata -->
                <div x-show="data && data.type === 'code-summary'" class="grid grid-cols-2 gap-4">
                    <div x-show="data && data.language" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                        <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase mb-1">Language</div>
                        <div class="text-sm font-medium" x-text="data?.language"></div>
                    </div>
                    <div x-show="data && data.file_path" class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1">File</div>
                        <div class="text-sm font-medium" x-text="data?.file_path"></div>
                    </div>
                </div>

                <!-- Room Metadata -->
                <div x-show="data && data.type === 'room'">
                    {{-- Badges Row --}}
                    <div class="flex flex-wrap gap-3 mb-4">
                        <template x-if="data?.status === 'completed'">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Completed
                            </span>
                        </template>
                        <template x-if="data?.status === 'in_progress'">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                In Progress
                            </span>
                        </template>
                        <template x-if="data?.status === 'retired'">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Retired
                            </span>
                        </template>
                        <template x-if="data?.difficulty">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold"
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': data?.difficulty === 'Easy',
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': data?.difficulty === 'Medium',
                                    'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300': data?.difficulty === 'Hard',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': data?.difficulty === 'Insane'
                                }"
                                x-text="data?.difficulty">
                            </span>
                        </template>
                        <template x-if="data?.time_spent">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="data?.time_spent"></span> min
                            </span>
                        </template>
                        <template x-if="data?.score_points_earned">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span x-text="data?.score_points_earned"></span> pts
                            </span>
                        </template>
                    </div>

                    {{-- Room Details Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div x-show="data && data.platform" class="flex items-start gap-3 p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-100 dark:border-purple-800">
                            <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wide mb-1">Platform</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="data?.platform"></div>
                            </div>
                        </div>
                        
                        <div x-show="data && data.completed_at" class="flex items-start gap-3 p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-100 dark:border-green-800">
                            <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">Completed</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="formatDate(data?.completed_at)"></div>
                            </div>
                        </div>

                        <div x-show="data && data.room_id_author" class="flex items-start gap-3 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                            <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Room ID / Author</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="data?.room_id_author"></div>
                            </div>
                        </div>

                        <div x-show="data && data.platform_username" class="flex items-start gap-3 p-4 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border border-amber-100 dark:border-amber-800">
                            <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-1">Platform Username</div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="data?.platform_username"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning & Purpose (Rooms Only) -->
                <div x-show="data && data.type === 'room' && data.objective_goal" class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-l-4 border-purple-500 dark:border-purple-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Objective / Goal
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed" x-html="formatContent(data?.objective_goal)"></p>
                </div>

                <div x-show="data && data.type === 'room' && data.key_techniques_used" class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-l-4 border-purple-500 dark:border-purple-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Key Techniques Used
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap" x-text="data?.key_techniques_used"></p>
                </div>

                <div x-show="data && data.type === 'room' && data.tools_commands_used" class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-l-4 border-purple-500 dark:border-purple-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        Tools / Commands Used
                    </h3>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <pre class="text-gray-700 dark:text-gray-300 font-mono text-sm leading-relaxed whitespace-pre-wrap" x-text="data?.tools_commands_used"></pre>
                    </div>
                </div>

                <div x-show="data && data.type === 'room' && data.attack_vector_summary" class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-l-4 border-purple-500 dark:border-purple-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Attack Vector Summary
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap" x-text="data?.attack_vector_summary"></p>
                </div>

                <div x-show="data && data.type === 'room' && data.flag_evidence_proof" class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-l-4 border-purple-500 dark:border-purple-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Flag Evidence / Proof
                    </h3>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap" x-text="data?.flag_evidence_proof"></p>
                    </div>
                </div>

                <div x-show="data && data.type === 'room' && data.difficulty_confirmation" class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-1">Difficulty Confirmation</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="data?.difficulty_confirmation"></div>
                </div>

                <!-- Reproducibility (Rooms Only) -->
                <div x-show="data && data.type === 'room' && data.walkthrough_summary_steps" class="p-6 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl border-l-4 border-orange-500 dark:border-orange-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M12 15h.01"/>
                        </svg>
                        Walkthrough Summary / Steps
                    </h3>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap" x-text="data?.walkthrough_summary_steps"></p>
                    </div>
                </div>

                <div x-show="data && data.type === 'room' && data.tools_environment" class="p-6 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl border-l-4 border-orange-500 dark:border-orange-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        Tools & Environment
                    </h3>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap" x-text="data?.tools_environment"></p>
                    </div>
                </div>

                <div x-show="data && data.type === 'room' && data.command_log_snippet" class="p-6 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl border-l-4 border-orange-500 dark:border-orange-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        Command Log / Snippet
                    </h3>
                    <div class="bg-gray-900 rounded-lg overflow-hidden border-2 border-gray-800 shadow-xl">
                        <div class="px-4 py-2 bg-gray-800 border-b border-gray-700 flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">Commands</span>
                        </div>
                        <pre class="p-4 overflow-x-auto"><code class="text-gray-100 font-mono text-sm leading-relaxed whitespace-pre-wrap" x-text="data?.command_log_snippet"></code></pre>
                    </div>
                </div>

                <div x-show="data && data.type === 'room' && data.completion_screenshot_report_link" class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-1">Completion Screenshot / Report</div>
                    <a :href="data?.completion_screenshot_report_link" target="_blank" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline" x-text="data?.completion_screenshot_report_link"></a>
                </div>

                <!-- Reflection (Rooms Only) -->
                <div x-show="data && data.type === 'room' && data.reflection_takeaways" class="p-6 bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 rounded-xl border-l-4 border-teal-500 dark:border-teal-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Reflection / Takeaways
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed" x-html="formatContent(data?.reflection_takeaways)"></p>
                </div>

                <!-- Key Objectives (Book Pages Only) -->
                <div x-show="data && data.type === 'book-page' && data.key_objectives" class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border-l-4 border-green-500 dark:border-green-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Key Objectives
                    </h3>
                    <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                        <template x-for="(obj, index) in (data?.key_objectives?.split('\n').filter(o => o.trim()) || [])" :key="index">
                            <li class="flex items-start gap-2">
                                <span class="text-green-600 dark:text-green-400 mt-1">•</span>
                                <span x-text="obj.trim()"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <!-- Summary Section -->
                <div x-show="data && data.summary" class="p-6 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Summary
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed" x-html="formatContent(data?.summary)"></p>
                </div>

                <!-- Reflection (Book Pages Only) -->
                <div x-show="data && data.type === 'book-page' && data.reflection" class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border-l-4 border-purple-500 dark:border-purple-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Reflection / Insight
                    </h3>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed" x-html="formatContent(data?.reflection)"></p>
                </div>

                <!-- Applied Snippet (Book Pages Only) -->
                <div x-show="data && data.type === 'book-page' && data.applied_snippet" class="p-6 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-xl border-l-4 border-indigo-500 dark:border-indigo-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                        Applied Snippet / Exercise
                    </h3>
                    <div class="bg-gray-900 rounded-lg overflow-hidden border-2 border-gray-800 shadow-xl">
                        <div class="px-4 py-2 bg-gray-800 border-b border-gray-700 flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">Code / Exercise</span>
                        </div>
                        <pre class="p-4 overflow-x-auto"><code class="text-gray-100 font-mono text-sm leading-relaxed whitespace-pre-wrap" x-text="data?.applied_snippet"></code></pre>
                    </div>
                </div>

                <!-- How to Run (Book Pages Only) -->
                <div x-show="data && data.type === 'book-page' && data.how_to_run" class="p-6 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-xl border-l-4 border-orange-500 dark:border-orange-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        How to Run / Recreate
                    </h3>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <pre class="text-gray-700 dark:text-gray-300 font-mono text-sm leading-relaxed whitespace-pre-wrap" x-text="data?.how_to_run"></pre>
                    </div>
                </div>

                <!-- Result / Evidence (Book Pages Only) -->
                <div x-show="data && data.type === 'book-page' && data.result_evidence" class="p-6 bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/20 dark:to-cyan-900/20 rounded-xl border-l-4 border-teal-500 dark:border-teal-400">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Result / Evidence
                    </h3>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap" x-text="data?.result_evidence"></p>
                    </div>
                </div>

                <!-- Content Section -->
                <div x-show="data && (data.content || data.description)" class="prose prose-lg dark:prose-invert max-w-none">
                    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <div x-show="data && data.content" x-html="formatContent(data?.content)" class="text-gray-700 dark:text-gray-300 leading-relaxed"></div>
                        <div x-show="data && data.description && !data.content" x-html="formatContent(data?.description)" class="text-gray-700 dark:text-gray-300 leading-relaxed"></div>
                    </div>
                </div>

                <!-- Code Block -->
                <div x-show="data && data.type === 'code-summary' && data.code" class="relative">
                    <div class="absolute top-3 right-3 flex gap-2">
                        <button class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-lg hover:bg-white dark:hover:bg-gray-800 transition-colors">
                            Copy
                        </button>
                    </div>
                    <div class="bg-gray-900 rounded-xl overflow-hidden border-2 border-gray-800 shadow-2xl">
                        <div class="px-4 py-2 bg-gray-800 border-b border-gray-700 flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2" x-text="data?.language || 'code'"></span>
                        </div>
                        <pre class="p-6 overflow-x-auto"><code class="text-gray-100 font-mono text-sm leading-relaxed" x-text="data?.code"></code></pre>
                    </div>
                </div>

                <!-- Tags Section -->
                <div x-show="data && data.tags && Array.isArray(data.tags) && data.tags.length > 0" class="pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Tags</h3>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tag in (data?.tags || [])" :key="tag?.id">
                            <span class="px-4 py-2 text-sm font-medium rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 cursor-default" x-text="tag?.name"></span>
                        </template>
                    </div>
                </div>

                <!-- Action Links -->
                <div x-show="data && (data.verify_url || data.repository_url || data.room_url || data.platform_profile_link)" class="flex flex-wrap gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a x-show="data && data.verify_url" :href="data?.verify_url" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Verify Certificate
                    </a>
                    <a x-show="data && data.repository_url" :href="data?.repository_url" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-gray-700 to-gray-800 text-white font-semibold rounded-xl hover:from-gray-800 hover:to-gray-900 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                        View Repository
                    </a>
                    <a x-show="data && data.room_url" :href="data?.room_url" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Visit Room
                    </a>
                    <a x-show="data && data.platform_profile_link" :href="data?.platform_profile_link" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-orange-600 text-white font-semibold rounded-xl hover:from-red-700 hover:to-orange-700 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        View Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div x-show="!loading && error" class="text-center py-20 px-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 mb-4">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Error Loading Content</h3>
            <p class="text-red-600 dark:text-red-400" x-text="error"></p>
        </div>
    </div>

    <script>
        function contentModalData() {
            return {
                loading: false,
                data: null,
                error: null,
                init() {
                    // Listen for load-content-modal event
                    window.addEventListener('load-content-modal', (event) => {
                        const { type, id, slug } = event.detail;
                        if (type && (id || slug)) {
                            this.loadContent(type, id, slug);
                        }
                    });
                    
                    // Also watch store for backward compatibility
                    if (this.$store && this.$store.modalContent) {
                        this.$watch('$store.modalContent', (content) => {
                            if (content) {
                                this.loadContent(content.type, content.id, content.slug);
                            }
                        });
                    }
                },
                async loadContent(type, id, slug) {
                    this.loading = true;
                    this.error = null;
                    this.data = null;

                    try {
                        let url = '';
                        // Map modal type to API path (plural form)
                        const apiTypeMap = {
                            'book-page': 'book-pages',
                            'code-summary': 'code-summaries',
                            'room': 'rooms',
                            'certificate': 'certificates',
                            'course': 'courses'
                        };
                        
                        const apiPath = apiTypeMap[type] || type;
                        
                        if (slug) {
                            // Encode slug to handle special characters
                            url = `/api/${apiPath}/${encodeURIComponent(slug)}`;
                        } else if (id) {
                            url = `/api/${apiPath}/${id}`;
                        } else {
                            throw new Error('No ID or slug provided');
                        }

                        console.log('Loading content from:', url, 'Type:', type, 'Slug:', slug);
                        const response = await fetch(url);
                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Failed to load content:', response.status, errorText);
                            throw new Error(`Failed to load content: ${response.status} ${response.statusText}`);
                        }
                        
                        this.data = await response.json();
                        console.log('Content loaded successfully:', this.data);
                    } catch (e) {
                        console.error('Error loading content:', e);
                        this.error = e.message || 'Failed to load content';
                        this.data = null;
                    } finally {
                        this.loading = false;
                    }
                },
                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                },
                formatContent(content) {
                    if (!content) return '';
                    return content.replace(/\n/g, '<br>');
                }
            }
        }
    </script>
</x-modal>
