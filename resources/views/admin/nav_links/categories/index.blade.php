@extends('layouts.app')
@section('title', ($link->getTranslated('title') ?: 'Untitled') . ' - Categories')
@section('content')
    @if(session('status'))
    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-md">
        <p class="text-green-800 font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 rounded-lg shadow-md">
        <p class="text-red-800 font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </p>
    </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.nav.index') }}" class="text-gray-600 hover:text-teal-600 transition-colors font-medium">{{ __('app.admin.nav_link.navigation') }}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('admin.nav.links.index', $nav) }}" class="text-gray-600 hover:text-teal-600 transition-colors font-medium">{{ $nav->getTranslated('label') ?: 'Untitled' }}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-semibold">{{ $link->getTranslated('title') ?: 'Untitled' }}</span>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-teal-600 via-cyan-600 to-blue-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">{{ $link->getTranslated('title') ?: 'Untitled' }}</h1>
                    <p class="text-teal-100 text-lg">{{ __('app.admin.categories.manage_categories') }}</p>
                </div>
                <div class="hidden md:block">
                    <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <button onclick="document.getElementById('attach-category-modal').classList.remove('hidden')" 
                class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-blue-500 text-blue-600 hover:bg-blue-50 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg transform hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            {{ __('app.admin.categories.attach_category') }}
        </button>
        <button onclick="openCategoryCreateModal()" 
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create New Category
        </button>
        <a href="{{ route('admin.nav.links.index', $nav) }}" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('app.admin.categories.back_to_sub_navigation') }}
        </a>
    </div>

    @if($link->categories->isEmpty())
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="py-20 text-center">
                <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('app.admin.categories.no_categories_yet') }}</h3>
                <p class="text-gray-600 mb-8 text-lg">{{ __('app.admin.categories.get_started') }}</p>
                <div class="flex items-center justify-center gap-4">
                    <button onclick="document.getElementById('attach-category-modal').classList.remove('hidden')" 
                            class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-blue-500 text-blue-600 hover:bg-blue-50 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg">
                        {{ __('app.admin.categories.attach_existing_category') }}
                    </button>
                    <button onclick="openCategoryCreateModal()" 
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl">
                        {{ __('app.admin.categories.create_new_category') }}
                    </button>
                </div>
            </div>
        </div>
    @else
        {{-- Categories Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($link->categories as $category)
                <div class="group bg-white rounded-xl shadow-lg border-2 border-gray-200 hover:border-teal-400 transition-all overflow-hidden hover:shadow-2xl transform hover:-translate-y-1">
                    {{-- Category Header with Color --}}
                    <div class="h-2" style="background: linear-gradient(90deg, {{ $category->color ?? '#3b82f6' }} 0%, {{ $category->color ?? '#3b82f6' }}dd 100%);"></div>
                    
                    <div class="p-6">
                        {{-- Category Name and Info --}}
                        <div class="mb-4">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $category->getTranslated('name') ?: $category->slug }}</h3>
                            @if($category->getTranslated('name') && $category->getTranslated('name') !== $category->slug)
                                <p class="text-xs text-gray-500 font-mono">{{ $category->slug }}</p>
                            @endif
                        </div>

                        {{-- Category Details --}}
                        <div class="space-y-2 mb-6">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <span class="font-medium">{{ __('app.admin.categories.position_label') }}</span>
                                <span>{{ $category->position ?? 0 }}</span>
                            </div>
                            @if($category->color)
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                    <span class="font-medium">{{ __('app.admin.categories.color_label') }}</span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-4 h-4 rounded border-2 border-gray-300 shadow-sm" style="background-color: {{ $category->color }}"></span>
                                        <span class="font-mono text-xs">{{ $category->color }}</span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Item Count Badge --}}
                        <div class="mb-6">
                            <a href="{{ route('admin.nav.links.categories.items.index', [$nav, $link, $category]) }}" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 rounded-lg font-medium hover:from-purple-200 hover:to-pink-200 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $category->items->count() }} {{ __('app.admin.categories.items') }}</span>
                            </a>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200">
                            <a href="{{ route('admin.nav.links.categories.items.index', [$nav, $link, $category]) }}" 
                               class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-xs font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ __('app.admin.categories.items') }}
                            </a>
                            <button onclick="openAnimationStyleModal({{ $category->id }}, {{ json_encode($category->getTranslated('name')) }}, {{ json_encode($category->animation_style ?? '') }})"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 text-white text-xs font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                                {{ __('app.admin.categories.style') }}
                            </button>
                            <button onclick="openEditModal({{ $category->id }}, {{ json_encode($category->name ?? ['en' => '', 'ja' => '']) }}, {{ json_encode($category->slug) }}, {{ json_encode($category->color ?? '') }}, {{ $category->position ?? 0 }}, {{ json_encode($category->summary ?? ['en' => '', 'ja' => '']) }}, {{ json_encode($category->image_path ? asset('storage/' . $category->image_path) : '') }}, {{ json_encode($category->document_path ? asset('storage/' . $category->document_path) : '') }})"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white text-xs font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                {{ __('app.admin.categories.edit') }}
                            </button>
                            <form action="{{ route('admin.nav.links.categories.destroy', [$nav, $link, $category]) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ __('app.admin.categories.remove_confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white text-xs font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    {{ __('app.admin.categories.remove') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Create Category Modal --}}
    <x-category-create-modal :navId="$nav->id" :linkId="$link->id" />

    {{-- Attach Category Modal --}}
    <div id="attach-category-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">{{ __('app.admin.categories.attach_existing_category') }}</h3>
                <button onclick="document.getElementById('attach-category-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form action="{{ route('admin.nav.links.categories.attach', [$nav, $link]) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.admin.categories.category') }}</label>
                        <select name="category_id" required class="w-full px-4 py-3 bg-white border-2 border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <option value="">{{ __('app.admin.categories.select_category') }}</option>
                            @foreach($allCategories as $category)
                                @if(!in_array($category->id, $assignedCategoryIds))
                            <option value="{{ $category->id }}">{{ $category->getTranslated('name') ?: $category->slug }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" onclick="document.getElementById('attach-category-modal').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            {{ __('app.admin.nav_link.cancel') }}
                        </button>
                        <button type="submit" class="px-6 py-2.5 text-sm font-semibold bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                            {{ __('app.admin.categories.attach') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Category Modal --}}
    <div id="edit-category-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full my-4">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-xl z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ __('app.admin.categories.edit_category') }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ __('app.admin.categories.manage_category_details') }}</p>
                    </div>
                    <button onclick="document.getElementById('edit-category-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <form id="edit-category-form" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="max-h-[75vh] overflow-y-auto px-6 py-6 space-y-6">
                    {{-- Section 1: Universal Settings (Top) --}}
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-5 border border-gray-200">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-1 h-6 bg-teal-500 rounded-full"></div>
                            <h4 class="text-base font-semibold text-gray-900">{{ __('app.admin.categories.universal_settings') }}</h4>
                            <span class="text-xs text-gray-500 bg-white px-2 py-0.5 rounded-full">{{ __('app.admin.categories.basic_info') }}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-dual-language-input 
                                    name="name" 
                                    label="{{ __('app.admin.categories.name_required') }}" 
                                    :value="['en' => '', 'ja' => '']"
                                    placeholder="e.g., Java Pages"
                                    :required="true"
                                />
                                <p class="text-xs text-gray-500 mt-1.5">{{ __('app.admin.categories.display_name_hint') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    {{ __('app.admin.categories.slug') }}
                                </label>
                                <div class="relative">
                                    <input type="text" id="edit-slug" name="slug" placeholder="{{ __('app.admin.categories.slug_auto_generated') }}" class="w-full px-4 py-2.5 pl-8 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-sm transition-all font-mono text-sm">
                                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-gray-500 mt-1.5">{{ __('app.admin.categories.url_friendly_identifier') }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('app.admin.categories.color') }}</label>
                                <div class="flex items-center gap-3">
                                    <input type="text" id="edit-color" name="color" placeholder="#3b82f6" class="flex-1 px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-sm font-mono text-sm">
                                    <input type="color" id="edit-color-picker" onchange="document.getElementById('edit-color').value = this.value" class="w-14 h-12 cursor-pointer bg-white border-2 border-gray-300 rounded-lg shadow-sm hover:border-teal-500 transition-colors">
                                    <div id="edit-color-preview" class="w-12 h-12 rounded-lg border-2 border-gray-300 shadow-sm" style="background-color: #3b82f6;"></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('app.admin.categories.position') }}</label>
                                <input type="number" id="edit-position" name="position" min="0" placeholder="0" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 shadow-sm">
                                <p class="text-xs text-gray-500 mt-1.5">{{ __('app.admin.categories.display_order_hint') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Category Thumbnail (Optional) --}}
                    <div class="bg-blue-50/50 rounded-xl p-5 border border-blue-100">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                            <h4 class="text-base font-semibold text-gray-900">{{ __('app.admin.categories.category_thumbnail') }}</h4>
                            <span class="text-xs text-gray-500 bg-white px-2 py-0.5 rounded-full">{{ __('app.admin.categories.icon_only') }}</span>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-yellow-800 font-medium mb-1">{{ __('app.admin.categories.image_optional_warning') }}</p>
                            <p class="text-xs text-yellow-700">
                                {{ __('app.admin.categories.image_optional_description') }}
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Optional Category Thumbnail --}}
                            <div class="bg-white rounded-lg p-4 border border-blue-200 shadow-sm">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline-block mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ __('app.admin.categories.category_thumbnail') }}
                                </label>
                                <input type="file" id="edit-image" name="image" accept="image/*" onchange="previewImage(this)" class="w-full px-3 py-2.5 bg-white border-2 border-dashed border-blue-300 rounded-lg text-gray-700 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer">
                                <div id="edit-image-preview" class="mt-3 hidden">
                                    <div class="relative inline-block">
                                        <img id="edit-image-preview-img" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-blue-200 shadow-md">
                                        <button type="button" onclick="clearImagePreview()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <label class="flex items-center gap-2 mt-3 text-sm text-gray-600 cursor-pointer hover:text-red-600 transition-colors">
                                        <input type="checkbox" id="edit-remove-image" name="remove_image" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span>Remove current thumbnail</span>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Optional: Small icon/thumbnail for this category only</p>
                            </div>
                            
                            {{-- Document Upload --}}
                            <div class="bg-white rounded-lg p-4 border border-blue-200 shadow-sm">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <svg class="w-4 h-4 inline-block mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Document/File Upload
                                </label>
                                <input type="file" id="edit-document" name="document" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="w-full px-3 py-2.5 bg-white border-2 border-dashed border-blue-300 rounded-lg text-gray-700 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer">
                                <div id="edit-document-preview" class="mt-3 hidden">
                                    <a id="edit-document-preview-link" href="" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 hover:underline font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        View current document
                                    </a>
                                    <label class="flex items-center gap-2 mt-3 text-sm text-gray-600 cursor-pointer hover:text-red-600 transition-colors">
                                        <input type="checkbox" id="edit-remove-document" name="remove_document" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span>Remove current document</span>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Upload PDF, DOC, or DOCX files</p>
                            </div>
                        </div>
                        
                        {{-- Summary Section --}}
                        <div class="mt-5 bg-white rounded-lg p-4 border border-blue-200 shadow-sm">
                            <x-dual-language-input 
                                name="summary" 
                                label="{{ __('app.admin.categories.summary_description') }}" 
                                :value="['en' => '', 'ja' => '']"
                                :placeholder="__('app.admin.categories.summary_placeholder')"
                                :rows="4"
                            />
                            <p class="text-xs text-gray-500 mt-2">{{ __('app.admin.categories.summary_hint') }}</p>
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-xl flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('edit-category-modal').classList.add('hidden')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        {{ __('app.admin.nav_link.cancel') }}
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('app.admin.categories.update_category') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, slug, color, position, summary, imagePath, documentPath) {
            // Handle name (can be array or string for backward compatibility)
            const nameEnInput = document.querySelector('input[name="name[en]"]');
            const nameJaInput = document.querySelector('input[name="name[ja]"]');
            
            if (nameEnInput && nameJaInput) {
                if (typeof name === 'object' && name !== null) {
                    nameEnInput.value = name.en || '';
                    nameJaInput.value = name.ja || '';
                } else {
                    // Backward compatibility: if name is a string, put it in English field
                    nameEnInput.value = name || '';
                    nameJaInput.value = '';
                }
                // Trigger input events to update Alpine.js
                nameEnInput.dispatchEvent(new Event('input', { bubbles: true }));
                nameJaInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            document.getElementById('edit-slug').value = slug || '';
            document.getElementById('edit-color').value = color || '';
            if (color) {
                document.getElementById('edit-color-picker').value = color;
                document.getElementById('edit-color-preview').style.backgroundColor = color;
            } else {
                document.getElementById('edit-color-preview').style.backgroundColor = '#3b82f6';
            }
            document.getElementById('edit-position').value = position || 0;
            
            // Handle summary (can be array or string for backward compatibility)
            const summaryEnInput = document.querySelector('textarea[name="summary[en]"]');
            const summaryJaInput = document.querySelector('textarea[name="summary[ja]"]');
            
            if (summaryEnInput && summaryJaInput) {
                if (typeof summary === 'object' && summary !== null) {
                    summaryEnInput.value = summary.en || '';
                    summaryJaInput.value = summary.ja || '';
                } else {
                    // Backward compatibility: if summary is a string, put it in English field
                    summaryEnInput.value = summary || '';
                    summaryJaInput.value = '';
                }
                summaryEnInput.dispatchEvent(new Event('input', { bubbles: true }));
                summaryJaInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            // Image preview
            const imagePreview = document.getElementById('edit-image-preview');
            const imagePreviewImg = document.getElementById('edit-image-preview-img');
            if (imagePath) {
                imagePreviewImg.src = imagePath;
                imagePreview.classList.remove('hidden');
            } else {
                imagePreview.classList.add('hidden');
            }
            
            // Document preview
            const documentPreview = document.getElementById('edit-document-preview');
            const documentPreviewLink = document.getElementById('edit-document-preview-link');
            if (documentPath) {
                documentPreviewLink.href = documentPath;
                documentPreview.classList.remove('hidden');
            } else {
                documentPreview.classList.add('hidden');
            }
            
            // Reset remove checkboxes
            document.getElementById('edit-remove-image').checked = false;
            document.getElementById('edit-remove-document').checked = false;
            
            
            // Set form action
            document.getElementById('edit-category-form').action = '{{ route("admin.nav.links.categories.update", [$nav, $link, ":category"]) }}'.replace(':category', id);
            document.getElementById('edit-category-modal').classList.remove('hidden');
        }

        // Color picker sync
        document.getElementById('edit-color-picker')?.addEventListener('change', function() {
            const color = this.value;
            document.getElementById('edit-color').value = color;
            document.getElementById('edit-color-preview').style.backgroundColor = color;
        });

        document.getElementById('edit-color')?.addEventListener('input', function() {
            const color = this.value;
            if (/^#[0-9A-F]{6}$/i.test(color)) {
                document.getElementById('edit-color-picker').value = color;
                document.getElementById('edit-color-preview').style.backgroundColor = color;
            }
        });

        // Image preview
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('edit-image-preview');
                    const previewImg = document.getElementById('edit-image-preview-img');
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                    document.getElementById('edit-remove-image').checked = false;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function clearImagePreview() {
            document.getElementById('edit-image').value = '';
            document.getElementById('edit-image-preview').classList.add('hidden');
            document.getElementById('edit-remove-image').checked = true;
        }

        // Close modals when clicking outside
        document.getElementById('attach-category-modal')?.addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
        document.getElementById('edit-category-modal')?.addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });

        // Function to open category create modal
        function openCategoryCreateModal() {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'category-create-modal' }));
        }

        // Animation Style Modal
        function openAnimationStyleModal(categoryId, categoryName, currentStyle) {
            document.getElementById('animation-category-id').value = categoryId;
            document.getElementById('animation-category-name').textContent = categoryName;
            document.getElementById('animation-style-select').value = currentStyle || '';
            document.getElementById('animation-style-modal').classList.remove('hidden');
        }

        function closeAnimationStyleModal() {
            document.getElementById('animation-style-modal').classList.add('hidden');
        }
    </script>

    {{-- Animation Style Selection Modal --}}
    <div id="animation-style-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ __('app.admin.categories.select_animation_style') }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ __('app.admin.categories.category_label') }} <span id="animation-category-name" class="font-medium text-gray-900"></span>
                        </p>
                    </div>
                    <button onclick="closeAnimationStyleModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.nav.links.categories.update-animation-style', [$nav, $link]) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                <input type="hidden" id="animation-category-id" name="category_id" value="">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ __('app.admin.categories.choose_animation_style') }}
                        </label>
                        <select 
                            id="animation-style-select" 
                            name="animation_style" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-base"
                        >
                            <option value="">{{ __('app.admin.categories.no_animation') }}</option>
                            <optgroup label="{{ __('app.admin.categories.custom_styles') }}">
                                <option value="certificates">Certificates - Editorial Grid Collage with Carousel</option>
                                <option value="courses">Courses - Alternating Cards Layout</option>
                                <option value="rooms">Rooms - Horizontal Scrollable Cards</option>
                            </optgroup>
                            <optgroup label="{{ __('app.admin.categories.standard_styles') }}">
                                <option value="grid_editorial_collage">Grid - Editorial Collage</option>
                                <option value="carousel_scroll_left">Carousel - Scroll Left</option>
                                <option value="carousel_scroll_right">Carousel - Scroll Right</option>
                            </optgroup>
                        </select>
                        <p class="text-sm text-gray-500 mt-2">
                            {{ __('app.admin.categories.select_style_hint') }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 mb-1">{{ __('app.admin.categories.about_animation_styles') }}</p>
                                <p class="text-xs text-gray-600">
                                    {{ __('app.admin.categories.animation_styles_description') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                    <button 
                        type="button" 
                        onclick="closeAnimationStyleModal()" 
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        {{ __('app.admin.nav_link.cancel') }}
                    </button>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 text-sm font-semibold bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-lg transition-all shadow-md hover:shadow-lg"
                    >
                        <svg class="w-4 h-4 inline-block mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('app.admin.categories.save_style') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Close animation modal when clicking outside
        document.getElementById('animation-style-modal')?.addEventListener('click', function(e) {
            if (e.target === this) closeAnimationStyleModal();
        });
    </script>
@endsection
