<x-modal name="testimonial-detail-modal" maxWidth="4xl">
    <div class="max-h-[90vh] overflow-y-auto" x-data="{
        testimonial: null,
        loading: true,
        testimonialId: null,
        currentImageIndex: 0,
        allImages: [],
        imageInterval: null,
        init() {
            window.addEventListener('load-testimonial-modal', (e) => {
                this.testimonialId = e.detail.id;
                this.loadTestimonial();
            });
        },
        async loadTestimonial() {
            if (!this.testimonialId) return;
            this.loading = true;
            this.currentImageIndex = 0;
            try {
                const response = await fetch(`/api/testimonials/${this.testimonialId}`);
                if (response.ok) {
                    this.testimonial = await response.json();
                    // Prepare all images array (main photo + additional images)
                    this.allImages = [];
                    if (this.testimonial.mainPhoto) {
                        this.allImages.push(this.testimonial.mainPhoto);
                    }
                    if (this.testimonial.images && this.testimonial.images.length > 0) {
                        // Add additional images, but skip if mainPhoto is already in images
                        this.testimonial.images.forEach(img => {
                            if (img !== this.testimonial.mainPhoto) {
                                this.allImages.push(img);
                            }
                        });
                    }
                    // Start slideshow if we have multiple images
                    if (this.allImages.length > 1) {
                        this.startSlideshow();
                    }
                } else {
                    console.error('Testimonial not found');
                    this.$dispatch('close-modal', 'testimonial-detail-modal');
                }
            } catch (error) {
                console.error('Error loading testimonial:', error);
                this.$dispatch('close-modal', 'testimonial-detail-modal');
            } finally {
                this.loading = false;
            }
        },
        startSlideshow() {
            // Clear any existing interval
            if (this.imageInterval) {
                clearInterval(this.imageInterval);
            }
            // Change image every 3 seconds
            this.imageInterval = setInterval(() => {
                this.currentImageIndex = (this.currentImageIndex + 1) % this.allImages.length;
            }, 3000);
        },
        stopSlideshow() {
            if (this.imageInterval) {
                clearInterval(this.imageInterval);
                this.imageInterval = null;
            }
        },
        getCurrentImage() {
            if (this.allImages.length === 0) return null;
            return this.allImages[this.currentImageIndex];
        }
    }">
        <!-- Loading State -->
        <div x-show="loading" class="p-16 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">{{ __('app.testimonials.loading') }}</p>
        </div>

        <!-- Testimonial Content -->
        <div x-show="!loading && testimonial" class="bg-white dark:bg-gray-800">
            <!-- Header Section -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('app.testimonials.details') }}</h2>
                <button 
                    @click="$dispatch('close-modal', 'testimonial-detail-modal'); stopSlideshow();" 
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
                <!-- Photo Slideshow -->
                <div x-show="getCurrentImage()" class="mb-8 -mx-6 relative" @mouseenter="stopSlideshow()" @mouseleave="startSlideshow()">
                    <div class="w-full min-h-[200px] flex items-center justify-center overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 relative py-4">
                        <img 
                            :src="getCurrentImage()" 
                            :alt="testimonial && testimonial.name ? testimonial.name : '{{ __('app.testimonials.name_not_available') }}'"
                            class="max-w-full w-auto h-auto object-contain transition-opacity duration-500"
                            style="max-height: 500px; object-fit: contain; object-position: center;"
                            x-show="getCurrentImage()"
                        />
                        <!-- Image indicators -->
                        <div x-show="allImages.length > 1" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 bg-black/30 px-3 py-1.5 rounded-full">
                            <template x-for="(img, index) in allImages" :key="index">
                                <button
                                    @click="currentImageIndex = index; startSlideshow();"
                                    class="w-2 h-2 rounded-full transition-all duration-300"
                                    :class="currentImageIndex === index ? 'bg-white w-6' : 'bg-white/50'"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Name, Title and Company Section -->
                <div class="mb-8">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-gray-100 mb-3 leading-tight" x-text="testimonial && testimonial.name ? testimonial.name : '{{ __('app.testimonials.name_not_available') }}'"></h1>
                    <div x-show="testimonial && (testimonial.title || testimonial.company)" class="flex items-center gap-3 flex-wrap">
                        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                        <div class="flex items-center gap-3 flex-wrap justify-center">
                            <p x-show="testimonial && testimonial.title" class="text-xl md:text-2xl text-blue-600 dark:text-blue-400 font-semibold" x-text="testimonial && testimonial.title ? testimonial.title : ''"></p>
                            <span x-show="testimonial && testimonial.title && testimonial.company" class="text-gray-400">•</span>
                            <p x-show="testimonial && testimonial.company" class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 font-medium" x-text="testimonial && testimonial.company ? testimonial.company : ''"></p>
                        </div>
                        <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                    </div>
                </div>

                <!-- Quote Section -->
                <div class="mb-8">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 md:p-8 border-l-4 border-blue-500">
                        <div class="flex items-start gap-4">
                            <svg class="w-8 h-8 text-blue-500 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.996 2.151c-2.432.917-3.996 3.638-3.996 7.151h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.432.917-3.996 3.638-3.996 7.151h4v10h-10z"/>
                            </svg>
                            <blockquote class="flex-1">
                                <p class="text-lg md:text-xl lg:text-2xl text-gray-800 dark:text-gray-200 leading-relaxed font-medium" x-html="testimonial && testimonial.quote ? ('&quot;' + testimonial.quote.replace(/\n/g, '<br>') + '&quot;') : '&quot;{{ __('app.testimonials.no_quote_available') }}&quot;'"></p>
                            </blockquote>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Name Card -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('app.testimonials.full_name') }}</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="testimonial && testimonial.name ? testimonial.name : '{{ __('app.testimonials.not_provided') }}'"></div>
                    </div>
                    
                    <!-- Title Card -->
                    <div x-show="testimonial && testimonial.title" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('app.testimonials.title') }}</div>
                        <div class="text-lg font-semibold text-blue-600 dark:text-blue-400" x-text="testimonial && testimonial.title ? testimonial.title : ''"></div>
                    </div>
                    
                    <!-- Company Card -->
                    <div x-show="testimonial && testimonial.company" class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('app.testimonials.company') }}</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="testimonial && testimonial.company ? testimonial.company : ''"></div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200 dark:border-gray-700 my-8"></div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between">
                    <a 
                        x-show="testimonial && testimonial.sns_url"
                        :href="testimonial && testimonial.sns_url ? testimonial.sns_url : '#'" 
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-all duration-200 text-base font-semibold shadow-sm hover:shadow-md"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        View Profile
                    </a>
                    
                    <button 
                        @click="$dispatch('close-modal', 'testimonial-detail-modal'); stopSlideshow();" 
                        class="px-6 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 text-base font-medium"
                    >
                        {{ __('app.common.close') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && !testimonial" class="p-16 text-center">
            <p class="text-gray-500 dark:text-gray-400">{{ __('app.testimonials.not_found') }}</p>
        </div>
    </div>
</x-modal>
