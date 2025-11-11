<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('app.admin.testimonials.edit_title') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('app.admin.testimonials.edit_description') }}</p>
            </div>
            <a href="{{ route('admin.testimonials.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ __('app.common.cancel') }}
            </a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Current Photos -->
                @if($testimonial->media->where('type', 'image')->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-orange-50 to-red-50 dark:from-gray-900/50 dark:to-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('app.admin.testimonials.current_photos') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('app.admin.testimonials.select_photos_delete') }}</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($testimonial->media->where('type', 'image') as $media)
                                <div class="relative group">
                                    <img src="{{ asset('storage/' . $media->path) }}" alt="{{ $testimonial->name }}" class="w-full h-32 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-700 group-hover:border-red-400 transition-colors" />
                                    <label class="absolute top-2 right-2 cursor-pointer">
                                        <input type="checkbox" name="delete_images[]" value="{{ $media->id }}" class="sr-only peer" />
                                        <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm peer-checked:bg-red-500 peer-checked:text-white text-gray-700 dark:text-gray-300 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span class="text-xs font-medium">{{ __('app.common.delete') }}</span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Main Content Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-900/50 dark:to-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('app.admin.testimonials.basic_information') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('app.admin.testimonials.essential_details') }}</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('app.admin.testimonials.full_name') }} <span class="text-red-500">*</span>
                                </label>
                            <input 
                                type="text" 
                                name="name" 
                                value="{{ old('name', $testimonial->name) }}" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                placeholder="{{ __('app.admin.testimonials.full_name') }}"
                                required 
                            />
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Company & Title Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-dual-language-input 
                                    name="company" 
                                    label="{{ __('app.admin.testimonials.company') }}" 
                                    :value="$testimonial->getTranslations('company')"
                                    placeholder="{{ __('app.admin.testimonials.company') }}"
                                />
                                @error('company')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('app.admin.testimonials.title_role') }}
                                </label>
                                <input 
                                    type="text" 
                                    name="title" 
                                    value="{{ old('title', $testimonial->title) }}" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                    placeholder="{{ __('app.admin.testimonials.title_role_placeholder') }}"
                                />
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('app.admin.testimonials.title_role_hint') }}</p>
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Quote -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('app.admin.testimonials.testimonial_quote') }} <span class="text-red-500">*</span>
                            </label>
                            <x-dual-language-input 
                                name="quote" 
                                label="{{ __('app.testimonials.quote') }}" 
                                :value="$testimonial->getTranslations('quote')"
                                placeholder="{{ __('app.admin.testimonials.quote_placeholder') }}"
                                :rows="5"
                                required
                            />
                            @error('quote')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Links & Media Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-gray-900/50 dark:to-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('app.admin.testimonials.links_media') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('app.admin.testimonials.social_profiles') }}</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- SNS URL -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                                {{ __('app.admin.testimonials.social_network_url') }}
                            </label>
                            <input 
                                type="url" 
                                name="sns_url" 
                                value="{{ old('sns_url', $testimonial->sns_url) }}" 
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                placeholder="{{ __('app.admin.testimonials.social_network_placeholder') }}"
                            />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('app.admin.testimonials.social_network_hint') }}</p>
                            @error('sns_url')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Photo URL & Position Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('app.admin.testimonials.photo_url') }}
                                </label>
                                <input 
                                    type="url" 
                                    name="photo_url" 
                                    value="{{ old('photo_url', $testimonial->photo_url) }}" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                    placeholder="{{ __('app.admin.testimonials.photo_url_placeholder') }}"
                                />
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('app.admin.testimonials.photo_url_hint') }}</p>
                                @error('photo_url')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('app.admin.testimonials.display_position') }}
                                </label>
                                <input 
                                    type="number" 
                                    name="position" 
                                    value="{{ old('position', $testimonial->position) }}" 
                                    min="0" 
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                                />
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('app.admin.testimonials.display_position_hint') }}</p>
                                @error('position')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Photo Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ __('app.admin.testimonials.upload_photos') }}
                            </label>
                            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                        <label class="relative cursor-pointer rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>{{ __('app.admin.testimonials.upload_multiple') }}</span>
                                            <input type="file" name="images[]" accept="image/*" multiple class="sr-only" />
                                        </label>
                                        <p class="pl-1">{{ __('app.admin.testimonials.drag_drop') }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.admin.testimonials.file_formats') }}</p>
                                </div>
                            </div>
                            @error('images.*')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Settings Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-900/50 dark:to-gray-800/50">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('app.admin.testimonials.publishing_settings') }}</h3>
                    </div>
                    <div class="p-6">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input 
                                    type="checkbox" 
                                    name="is_published" 
                                    value="1" 
                                    {{ old('is_published', $testimonial->is_published) ? 'checked' : '' }} 
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('app.admin.testimonials.publish_immediately') }}</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.admin.testimonials.publish_hint') }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.testimonials.index') }}" class="px-6 py-3 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        {{ __('app.common.cancel') }}
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('app.admin.testimonials.update_testimonial') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
