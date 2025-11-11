@extends('layouts.app')
@section('title','Add Item to '.($nav->getTranslated('label') ?: 'Untitled'))
@section('content')
    @if(session('status'))
    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
        <p class="text-green-800 font-medium">{{ session('status') }}</p>
    </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('admin.nav.index') }}" class="hover:text-teal-600 transition-colors">{{ __('app.admin.nav_link.navigation') }}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('admin.nav.links.index', $nav) }}" class="hover:text-teal-600 transition-colors">{{ $nav->getTranslated('label') ?: 'Untitled' }}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium">{{ __('app.admin.nav_link.create_new_item_title') }}</span>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('app.admin.nav_link.create_new_item') }}</h1>
        <p class="text-gray-600">{{ __('app.admin.nav_link.add_new_item_description') }} "{{ $nav->getTranslated('label') ?: 'Untitled' }}"</p>
    </div>

    <form method="POST" action="{{ route('admin.nav.links.store', $nav) }}" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- Basic Information Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('app.admin.nav_link.basic_information') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('app.admin.nav_link.essential_details') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <x-dual-language-input 
                        name="title" 
                        label="{{ __('app.admin.nav_link.title') }}" 
                        :value="old('title', ['en' => '', 'ja' => ''])"
                        :placeholder="__('app.admin.nav_link.title_placeholder')"
                        :required="true"
                    />
                    <p class="text-xs text-gray-500 mt-1.5">{{ __('app.admin.nav_link.title_hint') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.nav_link.position') }}
                    </label>
                    <input 
                        type="number" 
                        name="position" 
                        value="0" 
                        min="0"
                        placeholder="0"
                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm"
                    />
                    <p class="text-xs text-gray-500 mt-1.5">{{ __('app.admin.categories.display_order_hint') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.nav_link.progress_label') }}
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            name="progress" 
                            min="0" 
                            max="100" 
                            placeholder="{{ __('app.admin.nav_link.progress_placeholder') }}"
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm"
                        />
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400">%</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5">{{ __('app.admin.nav_link.progress_hint') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.nav_link.issued_at') }}
                    </label>
                    <input 
                        type="date" 
                        name="issued_at" 
                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm"
                    />
                    <p class="text-xs text-gray-500 mt-1.5">{{ __('app.admin.nav_link.issued_at_hint') }}</p>
                </div>
            </div>
        </div>

        {{-- Categories Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('app.admin.nav_link.categories') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('app.admin.nav_link.select_categories') }}</p>
                </div>
            </div>

            @if($categories->isEmpty())
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-900 mb-1">{{ __('app.admin.nav_link.no_categories_available') }}</p>
                            <p class="text-xs text-blue-700">
                                {{ __('app.admin.nav_link.categories_will_appear') }}
                                <a href="{{ route('admin.nav.links.categories.index', [$nav, $nav->links->first() ?? null]) }}" class="font-medium underline hover:text-blue-900">
                                    {{ __('app.admin.nav_link.manage_categories') }}
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 max-h-64 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-3 p-3 bg-white rounded-lg border-2 border-gray-200 hover:border-teal-300 hover:bg-teal-50/50 cursor-pointer transition-all group">
                                <input 
                                    type="checkbox" 
                                    name="categories[]" 
                                    value="{{ $cat->id }}" 
                                    class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 focus:ring-2" 
                                />
                                <span class="flex-1 text-sm font-medium text-gray-700 group-hover:text-teal-700">
                                    {{ $cat->getTranslated('name') ?: $cat->slug }}
                                </span>
                                @if($cat->color)
                                    <span 
                                        class="inline-block w-5 h-5 rounded-full shadow-sm border-2 border-white" 
                                        style="background-color: {{ $cat->color }}"
                                        title="{{ $cat->color }}"
                                    ></span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('app.admin.nav_link.select_categories_hint') }}
                </p>
            @endif
        </div>

        {{-- Media & Files Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('app.admin.nav_link.media_files') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('app.admin.nav_link.upload_images_documents') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Image Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.nav_link.image_optional') }}
                    </label>
                    <div class="relative">
                        <input 
                            type="file" 
                            name="image" 
                            accept="image/*" 
                            onchange="previewImage(this, 'image-preview')"
                            class="w-full px-4 py-3 bg-white border-2 border-dashed border-gray-300 rounded-lg text-gray-700 hover:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                        />
                    </div>
                    <div id="image-preview" class="mt-3 hidden">
                        <img id="image-preview-img" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-teal-200 shadow-md">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ __('app.admin.nav_link.image_hint') }}</p>
                </div>

                {{-- PDF Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.nav_link.pdf_document_optional') }}
                    </label>
                    <div class="relative">
                        <input 
                            type="file" 
                            name="document" 
                            accept="application/pdf" 
                            class="w-full px-4 py-3 bg-white border-2 border-dashed border-gray-300 rounded-lg text-gray-700 hover:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ __('app.admin.nav_link.pdf_hint') }}</p>
                </div>
            </div>
        </div>

        {{-- URLs Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('app.admin.nav_link.links_urls') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('app.admin.nav_link.external_links') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.nav_link.url_optional') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <input 
                            name="url" 
                            type="url"
                            placeholder="https://example.com"
                            class="w-full pl-10 pr-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('app.admin.nav_link.proof_url_optional') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <input 
                            name="proof_url" 
                            type="url"
                            placeholder="https://example.com/proof"
                            class="w-full pl-10 pr-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm"
                        />
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes Section --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('app.admin.nav_link.additional_notes') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('app.admin.nav_link.additional_information') }}</p>
                </div>
            </div>

            <div>
                <textarea 
                    name="notes" 
                    rows="5" 
                    placeholder="{{ __('app.admin.nav_link.notes_placeholder') }}"
                    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm resize-none"
                ></textarea>
                <p class="text-xs text-gray-500 mt-2">{{ __('app.admin.nav_link.notes_hint') }}</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <a 
                href="{{ route('admin.nav.links.index', $nav) }}" 
                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('app.common.cancel') }}
            </a>

            <button 
                type="submit" 
                class="inline-flex items-center gap-2 px-8 py-3 text-sm font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-all shadow-md hover:shadow-lg"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.admin.nav_link.create_item') }}
            </button>
        </div>
    </form>

    <script>
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    const previewImg = document.getElementById(previewId + '-img');
                    if (preview && previewImg) {
                        previewImg.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
