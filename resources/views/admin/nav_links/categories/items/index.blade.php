@extends('layouts.app')
@section('title', ($category->getTranslated('name') ?: $category->slug) . ' - ' . __('app.admin.categories.sections'))
@section('content')
    @if(session('status'))
    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
        <p class="text-green-800 font-medium">{{ session('status') }}</p>
    </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-600">
        <a href="{{ route('admin.nav.links.index', $nav) }}" class="hover:text-teal-600 transition-colors inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg border border-gray-300 transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>{{ __('app.admin.categories.back') }}</span>
        </a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('admin.nav.index') }}" class="hover:text-teal-600 transition-colors">{{ __('app.admin.nav_link.navigation') }}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        @php
            $navLabel = $nav->getTranslated('label', app()->getLocale()) ?: '';
            if (!is_string($navLabel)) {
                $labelArray = is_array($nav->label) ? $nav->label : [];
                $navLabel = $labelArray[app()->getLocale()] ?? $labelArray['en'] ?? $labelArray['ja'] ?? '';
            }
        @endphp
        <a href="{{ route('admin.nav.links.index', $nav) }}" class="hover:text-teal-600 transition-colors">{{ $navLabel }}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('admin.nav.links.categories.index', [$nav, $link]) }}" class="hover:text-teal-600 transition-colors">{{ $link->getTranslated('title') ?: 'Untitled' }}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700">{{ $category->getTranslated('name') ?: $category->slug }}</span>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium">{{ __('app.admin.categories.items') }}</span>
    </div>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $category->getTranslated('name') ?: $category->slug }} - {{ __('app.admin.categories.sections') }}</h1>
            <p class="text-sm text-gray-600 mt-1">{{ __('app.admin.categories.manage_sections_description') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="document.getElementById('create-item-modal').classList.remove('hidden')" 
                    class="inline-flex items-center gap-2 rounded-lg bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition-all shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('app.admin.categories.add_new_section') }}
            </button>
        </div>
    </div>

    {{-- All Available Content Items Summary --}}
    <x-ui.card class="mb-6">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.admin.categories.all_available_content_items') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="text-sm font-medium text-blue-900">{{ __('app.admin.categories.book_pages') }}</span>
                    </div>
                    <p class="text-2xl font-bold text-blue-600">{{ count($allBookPages) }}</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                        <span class="text-sm font-medium text-purple-900">{{ __('app.admin.categories.code_summaries') }}</span>
                    </div>
                    <p class="text-2xl font-bold text-purple-600">{{ count($allCodeSummaries) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm font-medium text-green-900">Rooms</span>
                    </div>
                    <p class="text-2xl font-bold text-green-600">{{ count($allRooms) }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-yellow-900">Certificates</span>
                    </div>
                    <p class="text-2xl font-bold text-yellow-600">{{ count($allCertificates) }}</p>
                </div>
                <div class="bg-cyan-50 rounded-lg p-4 border border-cyan-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="text-sm font-medium text-cyan-900">Courses</span>
                    </div>
                    <p class="text-2xl font-bold text-cyan-600">{{ count($allCourses) }}</p>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-4">{{ __('app.admin.categories.content_items_description') }}</p>
        </div>
    </x-ui.card>

    @if($category->items->isEmpty())
        <x-ui.card>
            <div class="py-16 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-600 mb-4 text-lg">{{ __('app.admin.categories.no_sections_yet') }}</p>
                <button onclick="document.getElementById('create-item-modal').classList.remove('hidden')" 
                        class="inline-flex items-center gap-2 rounded-lg bg-teal-600 hover:bg-teal-700 px-4 py-2 text-sm font-medium text-white transition-colors">
                    Add First Section
                </button>
            </div>
        </x-ui.card>
    @else
        {{-- Unified Sections with Content Accordion --}}
        <div class="space-y-4" x-data="{ openSections: [] }">
            @foreach($sectionsWithContent as $index => $sectionData)
                @php
                    $item = $sectionData['item'];
                    $bookPages = $sectionData['bookPages'];
                    $codeSummaries = $sectionData['codeSummaries'];
                    $rooms = $sectionData['rooms'];
                    $certificates = $sectionData['certificates'];
                    $courses = $sectionData['courses'];
                @endphp
                <x-ui.card class="overflow-hidden">
                    {{-- Section Header (Always Visible) --}}
                    <div class="p-6 cursor-pointer hover:bg-gray-50 transition-colors" 
                         @click="openSections.includes({{ $item->id }}) ? openSections = openSections.filter(id => id !== {{ $item->id }}) : openSections.push({{ $item->id }})">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-gray-400 transition-transform" 
                                         :class="{ 'rotate-90': openSections.includes({{ $item->id }}) }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                @if($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->getTranslated('title') ?: __('app.admin.categories.untitled_section') }}" class="w-16 h-16 object-cover rounded-lg">
                                @else
                                    <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $item->getTranslated('title') ?: __('app.admin.categories.untitled_section') }}</h3>
                                    @if($item->slug)
                                        <p class="text-xs text-gray-500 font-mono mt-1">{{ $item->slug }}</p>
                                    @endif
                                    @if($item->getTranslated('summary'))
                                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($item->getTranslated('summary'), 100) }}</p>
                                    @endif
                                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                        <span>{{ __('app.admin.categories.position_colon') }} {{ $item->position }}</span>
                                        <span>•</span>
                                        <span>{{ $bookPages->count() + $codeSummaries->count() + $rooms->count() + $certificates->count() + $courses->count() }} {{ __('app.admin.categories.content_items') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                                @php
                                    $itemTitle = $item->getTranslated('title', app()->getLocale()) ?: '';
                                    if (!is_string($itemTitle)) {
                                        $titleArray = is_array($item->title) ? $item->title : [];
                                        $itemTitle = $titleArray[app()->getLocale()] ?? $titleArray['en'] ?? $titleArray['ja'] ?? '';
                                    }
                                    $itemTitleForJs = json_encode($itemTitle);
                                    
                                    $itemSummary = $item->getTranslated('summary', app()->getLocale()) ?: '';
                                    if (!is_string($itemSummary)) {
                                        $summaryArray = is_array($item->summary) ? $item->summary : [];
                                        $itemSummary = $summaryArray[app()->getLocale()] ?? $summaryArray['en'] ?? $summaryArray['ja'] ?? '';
                                    }
                                    $itemSummaryForJs = json_encode($itemSummary);
                                @endphp
                                <button onclick="openAddContentModal({{ $item->id }}, {{ $itemTitleForJs }})" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-teal-600 hover:text-teal-700 hover:bg-teal-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('app.admin.categories.add_content') }}
                                </button>
                                <button onclick="openEditItemModal({{ $item->id }}, {{ json_encode($item->title) }}, {{ json_encode($item->slug) }}, null, {{ json_encode($item->url) }}, {{ json_encode($item->summary) }}, {{ json_encode($item->download_url) }}, {{ json_encode($item->view_url) }}, {{ json_encode($item->visit_url) }}, {{ $item->position }}, {{ json_encode($item->image_path ? asset('storage/' . $item->image_path) : '') }}, {{ $item->show_title ?? 'true' }}, {{ $item->show_description ?? 'true' }}, {{ $item->show_slug ?? 'false' }}, {{ $item->show_buttons ?? 'true' }}, {{ json_encode($item->button_settings ?? null) }}, {{ json_encode($item->linked_model_type) }}, {{ json_encode($item->linked_model_id) }})" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    {{ __('app.common.edit') }}
                                </button>
                                <form action="{{ route('admin.nav.links.categories.items.destroy', [$nav, $link, $category, $item]) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('app.admin.categories.delete_section_confirm') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        {{ __('app.common.delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Section Content (Collapsible) --}}
                    <div x-show="openSections.includes({{ $item->id }})" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="border-t border-gray-200 bg-gray-50">
                        @include('admin.nav_links.categories.items.partials.content-display', [
                            'item' => $item,
                            'bookPages' => $bookPages,
                            'codeSummaries' => $codeSummaries,
                            'rooms' => $rooms,
                            'certificates' => $certificates,
                            'courses' => $courses,
                            'allBookPages' => $allBookPages,
                            'allCodeSummaries' => $allCodeSummaries,
                            'allRooms' => $allRooms,
                            'allCertificates' => $allCertificates,
                            'allCourses' => $allCourses,
                            'nav' => $nav,
                            'link' => $link,
                            'category' => $category
                        ])
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif

    {{-- Create Item Modal --}}
    <div id="create-item-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] flex flex-col relative">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.categories.add_new_section') }}</h3>
                <button onclick="closeCreateItemModal(true)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            {{-- Scrollable Content --}}
            <form id="create-item-form" action="{{ route('admin.nav.links.categories.items.store', [$nav, $link, $category]) }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                @csrf
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <div class="space-y-4" x-data="createItemFormData()">
                    {{-- Title Field with Bilingual Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.admin.categories.title') }}</label>
                            <div class="flex items-center gap-2">
                                <select x-model="titleLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="en">🇬🇧 English</option>
                                    <option value="ja">🇯🇵 日本語</option>
                                </select>
                                <span x-show="translatingTitle" class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('app.common.translating') }}
                                </span>
                            </div>
                        </div>
                        <div x-show="titleLang === 'en'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100"
                             x-cloak>
                            <input 
                                type="text"
                                name="title[en]" 
                                x-model="titleEn"
                                @input="handleTitleInput($event.target.value, 'en')"
                                placeholder="{{ __('app.admin.categories.title_placeholder') }}" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_english_auto_translate') }}</p>
                        </div>
                        <div x-show="titleLang === 'ja'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100"
                             x-cloak>
                            <input 
                                type="text"
                                name="title[ja]" 
                                x-model="titleJa"
                                @input="handleTitleInput($event.target.value, 'ja')"
                                placeholder="{{ __('app.admin.categories.title_placeholder') }}" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.slug') }}</label>
                        <input type="text" name="slug" placeholder="{{ __('app.admin.categories.slug_placeholder') }}" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 font-mono text-sm">
                        <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.categories.slug_help') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.image') }}</label>
                        <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.url') }}</label>
                        <input type="url" name="url" id="create-item-url" placeholder="{{ __('app.admin.categories.url_placeholder') }}" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    {{-- Summary Field with Bilingual Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.admin.categories.summary') }}</label>
                            <div class="flex items-center gap-2">
                                <select x-model="summaryLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="en">🇬🇧 English</option>
                                    <option value="ja">🇯🇵 日本語</option>
                                </select>
                                <span x-show="translatingSummary" class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('app.common.translating') }}
                                </span>
                            </div>
                        </div>
                        <div x-show="summaryLang === 'en'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100"
                             x-cloak>
                            <textarea 
                                name="summary[en]" 
                                x-model="summaryEn"
                                @input="handleSummaryInput($event.target.value, 'en')"
                                rows="3" 
                                placeholder="{{ __('app.admin.categories.summary_placeholder') }}" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_english_auto_translate') }}</p>
                        </div>
                        <div x-show="summaryLang === 'ja'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100"
                             x-cloak>
                            <textarea 
                                name="summary[ja]" 
                                x-model="summaryJa"
                                @input="handleSummaryInput($event.target.value, 'ja')"
                                rows="3" 
                                placeholder="{{ __('app.admin.categories.summary_placeholder') }}" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.download_url') }}</label>
                            <input type="url" name="download_url" id="create-item-download-url" placeholder="{{ __('app.admin.categories.url_placeholder_short') }}" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.view_url') }}</label>
                            <input type="url" name="view_url" id="create-item-view-url" placeholder="{{ __('app.admin.categories.url_placeholder_short') }}" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.visit_url') }}</label>
                            <input type="url" name="visit_url" id="create-item-visit-url" placeholder="{{ __('app.admin.categories.url_placeholder_short') }}" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.position') }}</label>
                        <input type="number" name="position" value="0" min="0" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    
                    {{-- Note about content linking --}}
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <p class="text-xs text-gray-500">{{ __('app.admin.categories.after_create_note') }}</p>
                    </div>
                    
                    {{-- Display Settings Section --}}
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('app.admin.categories.display_settings') }}</h4>
                        
                        {{-- Visibility Toggles --}}
                        <div class="space-y-3 mb-4">
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.show_title') }}</span>
                                <input type="checkbox" name="show_title" value="1" checked class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.show_description') }}</span>
                                <input type="checkbox" name="show_description" value="1" checked class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.show_slug') }}</span>
                                <input type="checkbox" name="show_slug" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.show_buttons') }}</span>
                                <input type="checkbox" name="show_buttons" value="1" checked class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </label>
                        </div>
                        
                        {{-- Button Design Settings --}}
                        <div class="space-y-4">
                            <h5 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ __('app.admin.categories.button_design') }}</h5>
                            
                            {{-- Download Button --}}
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.enable_download_button') }}</span>
                                    <input type="checkbox" name="button_settings[download][enabled]" value="1" checked class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.color') }}</label>
                                        <input type="color" name="button_settings[download][color]" value="#10b981" class="w-full h-8 rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.size') }}</label>
                                        <select name="button_settings[download][size]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                            <option value="xs">{{ __('app.admin.categories.size_extra_small') }}</option>
                                            <option value="sm">{{ __('app.admin.categories.size_small') }}</option>
                                            <option value="md" selected>{{ __('app.admin.categories.size_medium') }}</option>
                                            <option value="lg">{{ __('app.admin.categories.size_large') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- View Button --}}
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.enable_view_button') }}</span>
                                    <input type="checkbox" name="button_settings[view][enabled]" value="1" checked class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.color') }}</label>
                                        <input type="color" name="button_settings[view][color]" value="#3b82f6" class="w-full h-8 rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.size') }}</label>
                                        <select name="button_settings[view][size]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                            <option value="xs">{{ __('app.admin.categories.size_extra_small') }}</option>
                                            <option value="sm">{{ __('app.admin.categories.size_small') }}</option>
                                            <option value="md" selected>{{ __('app.admin.categories.size_medium') }}</option>
                                            <option value="lg">{{ __('app.admin.categories.size_large') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Visit Button --}}
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.enable_visit_button') }}</span>
                                    <input type="checkbox" name="button_settings[visit][enabled]" value="1" checked class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.color') }}</label>
                                        <input type="color" name="button_settings[visit][color]" value="#8b5cf6" class="w-full h-8 rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.size') }}</label>
                                        <select name="button_settings[visit][size]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                            <option value="xs">{{ __('app.admin.categories.size_extra_small') }}</option>
                                            <option value="sm">{{ __('app.admin.categories.size_small') }}</option>
                                            <option value="md" selected>{{ __('app.admin.categories.size_medium') }}</option>
                                            <option value="lg">{{ __('app.admin.categories.size_large') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                
                {{-- Modal Footer --}}
                <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg flex items-center justify-end gap-3">
                    <button type="button" onclick="closeCreateItemModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        {{ __('app.common.cancel') }}
                    </button>
                    <button type="submit" id="create-item-submit-btn" class="px-4 py-2 text-sm font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-md transition-colors">
                        {{ __('app.admin.categories.create') }}
                    </button>
                </div>
            </form>
            
            {{-- Success Message (shown after creation) --}}
            <div id="create-item-success" class="hidden absolute inset-0 bg-white rounded-lg flex flex-col items-center justify-center z-50">
                <div class="text-center px-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.admin.categories.section_created_successfully') }}</h3>
                    <p class="text-sm text-gray-600 mb-6">{{ __('app.admin.categories.section_created_message') }}</p>
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" onclick="resetCreateItemForm()" class="px-5 py-2.5 text-sm font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition-colors">
                            {{ __('app.admin.categories.create_another') }}
                        </button>
                        <button type="button" onclick="closeCreateItemModal(true)" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            {{ __('app.admin.categories.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Item Modal --}}
    <div id="edit-item-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] flex flex-col">
            {{-- Modal Header --}}
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.categories.edit_section') }}</h3>
                <button onclick="document.getElementById('edit-item-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            {{-- Scrollable Content --}}
            <form id="edit-item-form" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                @csrf
                @method('PUT')
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <div class="space-y-4" x-data="editItemFormData()">
                    {{-- Title Field with Bilingual Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.admin.categories.title') }}</label>
                            <div class="flex items-center gap-2">
                                <select x-model="titleLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="en">🇬🇧 English</option>
                                    <option value="ja">🇯🇵 日本語</option>
                                </select>
                                <span x-show="translatingTitle" class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('app.common.translating') }}
                                </span>
                            </div>
                        </div>
                        <div x-show="titleLang === 'en'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100"
                             x-cloak>
                            <input 
                                type="text"
                                id="edit-item-title-en" 
                                name="title[en]" 
                                x-model="titleEn"
                                @input="handleTitleInput($event.target.value, 'en')"
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_english_auto_translate') }}</p>
                        </div>
                        <div x-show="titleLang === 'ja'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100"
                             x-cloak>
                            <input 
                                type="text"
                                id="edit-item-title-ja" 
                                name="title[ja]" 
                                x-model="titleJa"
                                @input="handleTitleInput($event.target.value, 'ja')"
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.slug') }}</label>
                        <input type="text" id="edit-item-slug" name="slug" placeholder="{{ __('app.admin.categories.slug_placeholder') }}" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 font-mono text-sm">
                        <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.categories.slug_help') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.image') }}</label>
                        <input type="file" id="edit-item-image" name="image" accept="image/*" onchange="previewItemImage(this)" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <div id="edit-item-image-preview" class="mt-2 hidden">
                            <img id="edit-item-image-preview-img" src="" alt="{{ __('app.admin.categories.preview') }}" class="w-32 h-32 object-cover rounded border border-gray-300">
                            <label class="flex items-center gap-2 mt-2 text-sm text-gray-600">
                                <input type="checkbox" id="edit-item-remove-image" name="remove_image" value="1" class="rounded">
                                <span>{{ __('app.admin.categories.remove_current_image') }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.url') }}</label>
                        <input type="url" id="edit-item-url" name="url" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    {{-- Summary Field with Bilingual Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">{{ __('app.admin.categories.summary') }}</label>
                            <div class="flex items-center gap-2">
                                <select x-model="summaryLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="en">🇬🇧 English</option>
                                    <option value="ja">🇯🇵 日本語</option>
                                </select>
                                <span x-show="translatingSummary" class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('app.common.translating') }}
                                </span>
                            </div>
                        </div>
                        <div x-show="summaryLang === 'en'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100"
                             x-cloak>
                            <textarea 
                                id="edit-item-summary-en" 
                                name="summary[en]" 
                                x-model="summaryEn"
                                @input="handleSummaryInput($event.target.value, 'en')"
                                rows="3" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_english_auto_translate') }}</p>
                        </div>
                        <div x-show="summaryLang === 'ja'" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0" 
                             x-transition:enter-end="opacity-100"
                             x-cloak>
                            <textarea 
                                id="edit-item-summary-ja" 
                                name="summary[ja]" 
                                x-model="summaryJa"
                                @input="handleSummaryInput($event.target.value, 'ja')"
                                rows="3" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.download_url') }}</label>
                            <input type="url" id="edit-item-download-url" name="download_url" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.view_url') }}</label>
                            <input type="url" id="edit-item-view-url" name="view_url" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.visit_url') }}</label>
                            <input type="url" id="edit-item-visit-url" name="visit_url" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.admin.categories.position') }}</label>
                        <input type="number" id="edit-item-position" name="position" min="0" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    
                    {{-- Linked Content Items Section --}}
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('app.admin.categories.linked_content_items') }}</h4>
                        <p class="text-xs text-gray-500 mb-3">{{ __('app.admin.categories.linked_content_items_description') }}</p>
                        
                        <div id="edit-item-linked-content" class="space-y-2">
                            {{-- Content items will be loaded here via JavaScript --}}
                            <p class="text-sm text-gray-500 italic">{{ __('app.admin.categories.loading_content_items') }}</p>
                        </div>
                    </div>
                    
                    {{-- Display Settings Section --}}
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('app.admin.categories.display_settings') }}</h4>
                        
                        {{-- Visibility Toggles --}}
                        <div class="space-y-3 mb-4">
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.show_title') }}</span>
                                <input type="checkbox" id="edit-item-show-title" name="show_title" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.show_description') }}</span>
                                <input type="checkbox" id="edit-item-show-description" name="show_description" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.show_slug') }}</span>
                                <input type="checkbox" id="edit-item-show-slug" name="show_slug" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.show_buttons') }}</span>
                                <input type="checkbox" id="edit-item-show-buttons" name="show_buttons" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                            </label>
                        </div>
                        
                        {{-- Button Design Settings --}}
                        <div class="space-y-4">
                            <h5 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ __('app.admin.categories.button_design') }}</h5>
                            
                            {{-- Download Button --}}
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.enable_download_button') }}</span>
                                    <input type="checkbox" id="edit-item-button-download-enabled" name="button_settings[download][enabled]" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.color') }}</label>
                                        <input type="color" id="edit-item-button-download-color" name="button_settings[download][color]" value="#10b981" class="w-full h-8 rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.size') }}</label>
                                        <select id="edit-item-button-download-size" name="button_settings[download][size]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                            <option value="xs">{{ __('app.admin.categories.size_extra_small') }}</option>
                                            <option value="sm">{{ __('app.admin.categories.size_small') }}</option>
                                            <option value="md" selected>{{ __('app.admin.categories.size_medium') }}</option>
                                            <option value="lg">{{ __('app.admin.categories.size_large') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- View Button --}}
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.enable_view_button') }}</span>
                                    <input type="checkbox" id="edit-item-button-view-enabled" name="button_settings[view][enabled]" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.color') }}</label>
                                        <input type="color" id="edit-item-button-view-color" name="button_settings[view][color]" value="#3b82f6" class="w-full h-8 rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.size') }}</label>
                                        <select id="edit-item-button-view-size" name="button_settings[view][size]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                            <option value="xs">{{ __('app.admin.categories.size_extra_small') }}</option>
                                            <option value="sm">{{ __('app.admin.categories.size_small') }}</option>
                                            <option value="md" selected>{{ __('app.admin.categories.size_medium') }}</option>
                                            <option value="lg">{{ __('app.admin.categories.size_large') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Visit Button --}}
                            <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700">{{ __('app.admin.categories.enable_visit_button') }}</span>
                                    <input type="checkbox" id="edit-item-button-visit-enabled" name="button_settings[visit][enabled]" value="1" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.color') }}</label>
                                        <input type="color" id="edit-item-button-visit-color" name="button_settings[visit][color]" value="#8b5cf6" class="w-full h-8 rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">{{ __('app.admin.categories.size') }}</label>
                                        <select id="edit-item-button-visit-size" name="button_settings[visit][size]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded">
                                            <option value="xs">{{ __('app.admin.categories.size_extra_small') }}</option>
                                            <option value="sm">{{ __('app.admin.categories.size_small') }}</option>
                                            <option value="md" selected>{{ __('app.admin.categories.size_medium') }}</option>
                                            <option value="lg">{{ __('app.admin.categories.size_large') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                
                {{-- Modal Footer --}}
                <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('edit-item-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        {{ __('app.common.cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-md transition-colors">
                        {{ __('app.admin.categories.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Alpine.js component for create item form with auto-translation
        document.addEventListener('alpine:init', () => {
            Alpine.data('createItemFormData', () => ({
                titleLang: '{{ app()->getLocale() }}',
                titleEn: '',
                titleJa: '',
                translatingTitle: false,
                titleTranslateTimeout: null,
                summaryLang: '{{ app()->getLocale() }}',
                summaryEn: '',
                summaryJa: '',
                translatingSummary: false,
                summaryTranslateTimeout: null,
                async translateText(text, fromLang, toLang, field) {
                    if (!text || text.trim().length === 0) return;
                    
                    if (field === 'title') {
                        this.translatingTitle = true;
                    } else {
                        this.translatingSummary = true;
                    }
                    
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const response = await fetch('/api/translate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                text: text,
                                from: fromLang,
                                to: toLang
                            })
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.translated) {
                                if (field === 'title') {
                                    if (toLang === 'en') {
                                        this.titleEn = data.translated;
                                    } else {
                                        this.titleJa = data.translated;
                                    }
                                } else {
                                    if (toLang === 'en') {
                                        this.summaryEn = data.translated;
                                    } else {
                                        this.summaryJa = data.translated;
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Translation error:', error);
                    } finally {
                        if (field === 'title') {
                            this.translatingTitle = false;
                        } else {
                            this.translatingSummary = false;
                        }
                    }
                },
                handleTitleInput(value, currentLang) {
                    if (currentLang === 'en') {
                        this.titleEn = value;
                        clearTimeout(this.titleTranslateTimeout);
                        this.titleTranslateTimeout = setTimeout(() => {
                            if (value && value.trim().length > 0) {
                                this.translateText(value, 'en', 'ja', 'title');
                            }
                        }, 1000);
                    } else {
                        this.titleJa = value;
                        clearTimeout(this.titleTranslateTimeout);
                        this.titleTranslateTimeout = setTimeout(() => {
                            if (value && value.trim().length > 0) {
                                this.translateText(value, 'ja', 'en', 'title');
                            }
                        }, 1000);
                    }
                },
                handleSummaryInput(value, currentLang) {
                    if (currentLang === 'en') {
                        this.summaryEn = value;
                        clearTimeout(this.summaryTranslateTimeout);
                        this.summaryTranslateTimeout = setTimeout(() => {
                            if (value && value.trim().length > 0) {
                                this.translateText(value, 'en', 'ja', 'summary');
                            }
                        }, 1000);
                    } else {
                        this.summaryJa = value;
                        clearTimeout(this.summaryTranslateTimeout);
                        this.summaryTranslateTimeout = setTimeout(() => {
                            if (value && value.trim().length > 0) {
                                this.translateText(value, 'ja', 'en', 'summary');
                            }
                        }, 1000);
                    }
                }
            }));
        });
        
        // Alpine.js component for edit item form with auto-translation
        document.addEventListener('alpine:init', () => {
            Alpine.data('editItemFormData', () => ({
                titleLang: '{{ app()->getLocale() }}',
                titleEn: '',
                titleJa: '',
                translatingTitle: false,
                titleTranslateTimeout: null,
                summaryLang: '{{ app()->getLocale() }}',
                summaryEn: '',
                summaryJa: '',
                translatingSummary: false,
                summaryTranslateTimeout: null,
                async translateText(text, fromLang, toLang, field) {
                    if (!text || text.trim().length === 0) return;
                    
                    if (field === 'title') {
                        this.translatingTitle = true;
                    } else {
                        this.translatingSummary = true;
                    }
                    
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        const response = await fetch('/api/translate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                text: text,
                                from: fromLang,
                                to: toLang
                            })
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.translated) {
                                if (field === 'title') {
                                    if (toLang === 'en') {
                                        this.titleEn = data.translated;
                                    } else {
                                        this.titleJa = data.translated;
                                    }
                                } else {
                                    if (toLang === 'en') {
                                        this.summaryEn = data.translated;
                                    } else {
                                        this.summaryJa = data.translated;
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Translation error:', error);
                    } finally {
                        if (field === 'title') {
                            this.translatingTitle = false;
                        } else {
                            this.translatingSummary = false;
                        }
                    }
                },
                handleTitleInput(value, currentLang) {
                    if (currentLang === 'en') {
                        this.titleEn = value;
                        clearTimeout(this.titleTranslateTimeout);
                        this.titleTranslateTimeout = setTimeout(() => {
                            if (value && value.trim().length > 0) {
                                this.translateText(value, 'en', 'ja', 'title');
                            }
                        }, 1000);
                    } else {
                        this.titleJa = value;
                        clearTimeout(this.titleTranslateTimeout);
                        this.titleTranslateTimeout = setTimeout(() => {
                            if (value && value.trim().length > 0) {
                                this.translateText(value, 'ja', 'en', 'title');
                            }
                        }, 1000);
                    }
                },
                handleSummaryInput(value, currentLang) {
                    if (currentLang === 'en') {
                        this.summaryEn = value;
                        clearTimeout(this.summaryTranslateTimeout);
                        this.summaryTranslateTimeout = setTimeout(() => {
                            if (value && value.trim().length > 0) {
                                this.translateText(value, 'en', 'ja', 'summary');
                            }
                        }, 1000);
                    } else {
                        this.summaryJa = value;
                        clearTimeout(this.summaryTranslateTimeout);
                        this.summaryTranslateTimeout = setTimeout(() => {
                            if (value && value.trim().length > 0) {
                                this.translateText(value, 'ja', 'en', 'summary');
                            }
                        }, 1000);
                    }
                }
            }));
        });
        
        // Translation strings for JavaScript
        const translations = {
            addContentToSection: @json(__('app.admin.categories.add_content_to_section')),
            loadingContentItems: @json(__('app.admin.categories.loading_content_items')),
            noContentItemsLinked: @json(__('app.admin.categories.no_content_items_linked')),
            errorLoadingContentItems: @json(__('app.admin.categories.error_loading_content_items')),
        };
        
        function openEditItemModal(id, title, slug, navLinkId, url, summary, downloadUrl, viewUrl, visitUrl, position, imagePath, showTitle, showDescription, showSlug, showButtons, buttonSettings, linkedModelType, linkedModelId) {
            // Handle title - can be string or array
            let titleEn = '', titleJa = '';
            if (typeof title === 'string') {
                titleEn = title;
            } else if (title && typeof title === 'object') {
                titleEn = title.en || '';
                titleJa = title.ja || '';
            }
            
            // Handle summary - can be string or array
            let summaryEn = '', summaryJa = '';
            if (typeof summary === 'string') {
                summaryEn = summary;
            } else if (summary && typeof summary === 'object') {
                summaryEn = summary.en || '';
                summaryJa = summary.ja || '';
            }
            
            // Set values using Alpine.js component
            const editForm = document.querySelector('#edit-item-form [x-data*="editItemFormData"]');
            if (editForm && editForm.__x) {
                editForm.__x.$data.titleEn = titleEn;
                editForm.__x.$data.titleJa = titleJa;
                editForm.__x.$data.summaryEn = summaryEn;
                editForm.__x.$data.summaryJa = summaryJa;
            } else {
                // Fallback: set directly on inputs
                const titleEnInput = document.getElementById('edit-item-title-en');
                const titleJaInput = document.getElementById('edit-item-title-ja');
                const summaryEnInput = document.getElementById('edit-item-summary-en');
                const summaryJaInput = document.getElementById('edit-item-summary-ja');
                
                if (titleEnInput) titleEnInput.value = titleEn;
                if (titleJaInput) titleJaInput.value = titleJa;
                if (summaryEnInput) summaryEnInput.value = summaryEn;
                if (summaryJaInput) summaryJaInput.value = summaryJa;
            }
            
            document.getElementById('edit-item-slug').value = slug || '';
            document.getElementById('edit-item-url').value = url || '';
            document.getElementById('edit-item-download-url').value = downloadUrl || '';
            document.getElementById('edit-item-view-url').value = viewUrl || '';
            document.getElementById('edit-item-visit-url').value = visitUrl || '';
            document.getElementById('edit-item-position').value = position || 0;
            
            // Load linked content items
            loadLinkedContentItems(id);
            
            // Display settings
            document.getElementById('edit-item-show-title').checked = showTitle !== undefined ? showTitle : true;
            document.getElementById('edit-item-show-description').checked = showDescription !== undefined ? showDescription : true;
            document.getElementById('edit-item-show-slug').checked = showSlug !== undefined ? showSlug : false;
            document.getElementById('edit-item-show-buttons').checked = showButtons !== undefined ? showButtons : true;
            
            // Button settings
            if (buttonSettings) {
                if (buttonSettings.download) {
                    document.getElementById('edit-item-button-download-enabled').checked = buttonSettings.download.enabled !== undefined ? buttonSettings.download.enabled : true;
                    document.getElementById('edit-item-button-download-color').value = buttonSettings.download.color || '#10b981';
                    document.getElementById('edit-item-button-download-size').value = buttonSettings.download.size || 'md';
                }
                if (buttonSettings.view) {
                    document.getElementById('edit-item-button-view-enabled').checked = buttonSettings.view.enabled !== undefined ? buttonSettings.view.enabled : true;
                    document.getElementById('edit-item-button-view-color').value = buttonSettings.view.color || '#3b82f6';
                    document.getElementById('edit-item-button-view-size').value = buttonSettings.view.size || 'md';
                }
                if (buttonSettings.visit) {
                    document.getElementById('edit-item-button-visit-enabled').checked = buttonSettings.visit.enabled !== undefined ? buttonSettings.visit.enabled : true;
                    document.getElementById('edit-item-button-visit-color').value = buttonSettings.visit.color || '#8b5cf6';
                    document.getElementById('edit-item-button-visit-size').value = buttonSettings.visit.size || 'md';
                }
            }
            
            const imagePreview = document.getElementById('edit-item-image-preview');
            const imagePreviewImg = document.getElementById('edit-item-image-preview-img');
            if (imagePath) {
                imagePreviewImg.src = imagePath;
                imagePreview.classList.remove('hidden');
            } else {
                imagePreview.classList.add('hidden');
            }
            
            document.getElementById('edit-item-remove-image').checked = false;
            document.getElementById('edit-item-form').action = '{{ route("admin.nav.links.categories.items.update", [$nav, $link, $category, ":item"]) }}'.replace(':item', id);
            document.getElementById('edit-item-modal').classList.remove('hidden');
        }

        function previewItemImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('edit-item-image-preview');
                    const previewImg = document.getElementById('edit-item-image-preview-img');
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                    document.getElementById('edit-item-remove-image').checked = false;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Available items data
        const availableItems = {
            'App\\Models\\BookPage': @js($allBookPages ?? []),
            'App\\Models\\CodeSummary': @js($allCodeSummaries ?? []),
            'App\\Models\\Room': @js($allRooms ?? []),
            'App\\Models\\Certificate': @js($allCertificates ?? []),
            'App\\Models\\Course': @js($allCourses ?? [])
        };
        
        // Auto-populate URLs when Model Type and ID are selected
        function setupAutoUrlPopulate() {
            // Create modal
            const createModelType = document.getElementById('create-linked-model-type');
            const createModelId = document.getElementById('create-linked-model-id');
            const createContainer = document.getElementById('create-item-select-container');
            
            if (createModelType && createModelId) {
                createModelType.addEventListener('change', function() {
                    const modelType = this.value;
                    if (modelType) {
                        // Show dropdown and populate options
                        createContainer.classList.remove('hidden');
                        populateItemDropdown(createModelId, modelType, 'create');
                    } else {
                        createContainer.classList.add('hidden');
                        createModelId.value = '';
                    }
                });
                
                createModelId.addEventListener('change', function() {
                    if (createModelType.value && this.value) {
                        fetchContentAndPopulateUrls(createModelType.value, this.value, 'create');
                    }
                });
            }
            
            // Edit modal
            const editModelType = document.getElementById('edit-linked-model-type');
            const editModelId = document.getElementById('edit-linked-model-id');
            const editContainer = document.getElementById('edit-item-select-container');
            
            if (editModelType && editModelId) {
                editModelType.addEventListener('change', function() {
                    const modelType = this.value;
                    if (modelType) {
                        // Show dropdown and populate options
                        editContainer.classList.remove('hidden');
                        populateItemDropdown(editModelId, modelType, 'edit');
                    } else {
                        editContainer.classList.add('hidden');
                        editModelId.value = '';
                    }
                });
                
                editModelId.addEventListener('change', function() {
                    if (editModelType.value && this.value) {
                        fetchContentAndPopulateUrls(editModelType.value, this.value, 'edit');
                    }
                });
            }
        }
        
        function populateItemDropdown(selectElement, modelType, modalType) {
            // Clear existing options
            selectElement.innerHTML = '<option value="">Choose an item...</option>';
            
            const items = availableItems[modelType] || [];
            items.forEach(item => {
                const option = document.createElement('option');
                // Use slug for BookPage, CodeSummary, Room; use ID for Certificate, Course
                const identifier = item.slug || item.id;
                option.value = identifier;
                option.textContent = item.title || `Item #${item.id}`;
                selectElement.appendChild(option);
            });
        }
        
        async function fetchContentAndPopulateUrls(modelType, identifier, modalType) {
            const prefix = modalType === 'create' ? 'create' : 'edit';
            const urlField = document.getElementById(`${prefix}-item-url`);
            const downloadUrlField = document.getElementById(`${prefix}-item-download-url`);
            const viewUrlField = document.getElementById(`${prefix}-item-view-url`);
            const visitUrlField = document.getElementById(`${prefix}-item-visit-url`);
            
            if (!urlField) return;
            
            try {
                let apiUrl = '';
                
                // Determine API endpoint based on model type
                // For BookPage, CodeSummary, Room: use slug; For Certificate, Course: use ID
                if (modelType === 'App\\Models\\BookPage') {
                    apiUrl = `/api/book-pages/${identifier}`;
                } else if (modelType === 'App\\Models\\CodeSummary') {
                    apiUrl = `/api/code-summaries/${identifier}`;
                } else if (modelType === 'App\\Models\\Room') {
                    apiUrl = `/api/rooms/${identifier}`;
                } else if (modelType === 'App\\Models\\Certificate') {
                    apiUrl = `/api/certificates/${identifier}`;
                } else if (modelType === 'App\\Models\\Course') {
                    apiUrl = `/api/courses/${identifier}`;
                } else {
                    return;
                }
                
                const response = await fetch(apiUrl);
                if (!response.ok) {
                    console.warn('Content not found or invalid identifier:', apiUrl);
                    return;
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Expected JSON but got:', contentType);
                    return;
                }
                
                const data = await response.json();
                const baseUrl = window.location.origin;
                
                // Auto-populate URL field - this is the main URL users will click (user-facing page)
                if (!urlField.value || urlField.value.includes('localhost:8000/login') || urlField.value.includes('localhost:8000/aja')) {
                    if (modelType === 'App\\Models\\BookPage') {
                        urlField.value = `${baseUrl}/book-pages/${identifier}`;
                    } else if (modelType === 'App\\Models\\CodeSummary') {
                        urlField.value = `${baseUrl}/code-summaries/${identifier}`;
                    } else if (modelType === 'App\\Models\\Room') {
                        urlField.value = `${baseUrl}/rooms/${identifier}`;
                    } else if (modelType === 'App\\Models\\Certificate') {
                        urlField.value = data.verify_url || `${baseUrl}/api/certificates/${identifier}`;
                    } else if (modelType === 'App\\Models\\Course') {
                        urlField.value = data.verify_url || `${baseUrl}/api/courses/${identifier}`;
                    }
                }
                
                // Auto-populate Download URL if available
                if (downloadUrlField && (!downloadUrlField.value || downloadUrlField.value.includes('localhost:8000/login'))) {
                    if (data.repository_url) {
                        downloadUrlField.value = data.repository_url;
                    } else if (data.file_path) {
                        downloadUrlField.value = `${baseUrl}/storage/${data.file_path}`;
                    }
                }
                
                // Auto-populate View URL if available
                if (viewUrlField && (!viewUrlField.value || viewUrlField.value.includes('localhost:8000/login'))) {
                    if (modelType === 'App\\Models\\Room' && data.room_url) {
                        viewUrlField.value = data.room_url;
                    } else if (data.verify_url) {
                        viewUrlField.value = data.verify_url;
                    } else {
                        viewUrlField.value = `${baseUrl}${apiUrl}`;
                    }
                }
                
                // Auto-populate Visit URL if available
                if (visitUrlField && (!visitUrlField.value || visitUrlField.value.includes('localhost:8000/login'))) {
                    if (modelType === 'App\\Models\\Room' && data.room_url) {
                        visitUrlField.value = data.room_url;
                    } else if (data.verify_url) {
                        visitUrlField.value = data.verify_url;
                    } else if (data.repository_url) {
                        visitUrlField.value = data.repository_url;
                    }
                }
                
            } catch (error) {
                console.error('Error fetching content:', error);
            }
        }
        
        // Initialize auto-populate on page load
        setupAutoUrlPopulate();
        
        // Load linked content items for a section
        async function loadLinkedContentItems(sectionId) {
            const container = document.getElementById('edit-item-linked-content');
            container.innerHTML = '<p class="text-sm text-gray-500 italic">' + translations.loadingContentItems + '</p>';
            
            try {
                const response = await fetch(`/api/sections/${sectionId}/content`);
                if (!response.ok) {
                    throw new Error('Failed to load content');
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Expected JSON but got HTML:', text.substring(0, 200));
                    throw new Error('Server returned HTML instead of JSON');
                }
                
                const data = await response.json();
                let html = '';
                
                if (!data.bookPages?.length && !data.codeSummaries?.length && !data.rooms?.length && !data.certificates?.length && !data.courses?.length) {
                    html = '<p class="text-sm text-gray-500 italic">' + translations.noContentItemsLinked + '</p>';
                } else {
                    html = '<div class="space-y-3">';
                    
                    // Book Pages
                    if (data.bookPages && data.bookPages.length > 0) {
                        html += '<div><p class="text-xs font-semibold text-gray-600 uppercase mb-1">Book Pages (' + data.bookPages.length + ')</p>';
                        data.bookPages.forEach(item => {
                            html += `<div class="flex items-center justify-between p-2 bg-blue-50 rounded text-sm">
                                <span class="text-gray-900">${item.title}</span>
                                <form action="{{ route('admin.sections.detach', ':section') }}" method="POST" class="inline" onsubmit="return confirm('Remove this item from section?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="sectionable_type" value="App\\Models\\BookPage">
                                    <input type="hidden" name="sectionable_id" value="${item.id}">
                                    <button type="submit" class="text-red-600 hover:text-red-700 text-xs">Remove</button>
                                </form>
                            </div>`;
                        });
                        html += '</div>';
                    }
                    
                    // Code Summaries
                    if (data.codeSummaries && data.codeSummaries.length > 0) {
                        html += '<div><p class="text-xs font-semibold text-gray-600 uppercase mb-1">Code Summaries (' + data.codeSummaries.length + ')</p>';
                        data.codeSummaries.forEach(item => {
                            html += `<div class="flex items-center justify-between p-2 bg-purple-50 rounded text-sm">
                                <span class="text-gray-900">${item.title}</span>
                                <form action="{{ route('admin.sections.detach', ':section') }}" method="POST" class="inline" onsubmit="return confirm('Remove this item from section?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="sectionable_type" value="App\\Models\\CodeSummary">
                                    <input type="hidden" name="sectionable_id" value="${item.id}">
                                    <button type="submit" class="text-red-600 hover:text-red-700 text-xs">Remove</button>
                                </form>
                            </div>`;
                        });
                        html += '</div>';
                    }
                    
                    // Rooms
                    if (data.rooms && data.rooms.length > 0) {
                        html += '<div><p class="text-xs font-semibold text-gray-600 uppercase mb-1">Rooms (' + data.rooms.length + ')</p>';
                        data.rooms.forEach(item => {
                            html += `<div class="flex items-center justify-between p-2 bg-green-50 rounded text-sm">
                                <span class="text-gray-900">${item.title}</span>
                                <form action="{{ route('admin.sections.detach', ':section') }}" method="POST" class="inline" onsubmit="return confirm('Remove this item from section?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="sectionable_type" value="App\\Models\\Room">
                                    <input type="hidden" name="sectionable_id" value="${item.id}">
                                    <button type="submit" class="text-red-600 hover:text-red-700 text-xs">Remove</button>
                                </form>
                            </div>`;
                        });
                        html += '</div>';
                    }
                    
                    // Certificates
                    if (data.certificates && data.certificates.length > 0) {
                        html += '<div><p class="text-xs font-semibold text-gray-600 uppercase mb-1">Certificates (' + data.certificates.length + ')</p>';
                        data.certificates.forEach(item => {
                            html += `<div class="flex items-center justify-between p-2 bg-yellow-50 rounded text-sm">
                                <span class="text-gray-900">${item.title}</span>
                                <form action="{{ route('admin.sections.detach', ':section') }}" method="POST" class="inline" onsubmit="return confirm('Remove this item from section?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="sectionable_type" value="App\\Models\\Certificate">
                                    <input type="hidden" name="sectionable_id" value="${item.id}">
                                    <button type="submit" class="text-red-600 hover:text-red-700 text-xs">Remove</button>
                                </form>
                            </div>`;
                        });
                        html += '</div>';
                    }
                    
                    // Courses
                    if (data.courses && data.courses.length > 0) {
                        html += '<div><p class="text-xs font-semibold text-gray-600 uppercase mb-1">Courses (' + data.courses.length + ')</p>';
                        data.courses.forEach(item => {
                            html += `<div class="flex items-center justify-between p-2 bg-cyan-50 rounded text-sm">
                                <span class="text-gray-900">${item.title}</span>
                                <form action="{{ route('admin.sections.detach', ':section') }}" method="POST" class="inline" onsubmit="return confirm('Remove this item from section?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="sectionable_type" value="App\\Models\\Course">
                                    <input type="hidden" name="sectionable_id" value="${item.id}">
                                    <button type="submit" class="text-red-600 hover:text-red-700 text-xs">Remove</button>
                                </form>
                            </div>`;
                        });
                        html += '</div>';
                    }
                    
                    html += '</div>';
                }
                
                container.innerHTML = html;
                
                // Replace :section placeholder in form actions
                container.querySelectorAll('form').forEach(form => {
                    form.action = form.action.replace(':section', sectionId);
                });
            } catch (error) {
                console.error('Error loading linked content:', error);
                container.innerHTML = '<p class="text-sm text-red-500">' + translations.errorLoadingContentItems + '</p>';
            }
        }
        
        // Close modals when clicking outside
        document.getElementById('create-item-modal')?.addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
        document.getElementById('edit-item-modal')?.addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
        
        // Content management functions
        function openAddContentModal(sectionId, sectionTitle) {
            const modal = document.getElementById('add-content-modal');
            const modalTitle = modal.querySelector('#add-content-modal-title');
            const form = modal.querySelector('#add-content-form');
            
            modalTitle.textContent = translations.addContentToSection + ': ' + sectionTitle;
            form.action = `{{ route('admin.sections.attach', ':id') }}`.replace(':id', sectionId);
            
            // Reset form
            document.getElementById('content-type-select').value = '';
            ['book-pages-select', 'code-summaries-select', 'rooms-select', 'certificates-select', 'courses-select'].forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });
            ['book-page-select', 'code-summary-select', 'room-select', 'certificate-select', 'course-select'].forEach(id => {
                const select = document.getElementById(id);
                if (select) select.value = '';
            });
            
            modal.classList.remove('hidden');
        }
        
        function showCourseSuccessModal() {
            const modalContent = document.getElementById('create-modal-content');
            const modalTitle = document.getElementById('create-modal-title');
            
            if (modalContent && modalTitle) {
                modalTitle.textContent = 'Course Created Successfully!';
                modalContent.innerHTML = `
                    <div class="text-center py-12">
                        <div class="mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-cyan-500 shadow-lg mb-6">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-3">Course Created Successfully!</h3>
                            <p class="text-lg text-gray-600 mb-10">Your course has been added successfully. What would you like to do next?</p>
                        </div>
                        <div class="flex items-center justify-center gap-4">
                            <button 
                                onclick="closeModal('create-content-modal'); window.location.reload();" 
                                class="px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                See the List
                            </button>
                            <button 
                                onclick="closeModal('create-content-modal'); openCreateModal('course');" 
                                class="px-8 py-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-400 shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create Another
                            </button>
                        </div>
                    </div>
                `;
            }
        }
        
        function showRoomSuccessModal() {
            const modalContent = document.getElementById('create-modal-content');
            const modalTitle = document.getElementById('create-modal-title');
            
            if (modalContent && modalTitle) {
                modalTitle.textContent = 'Room Created Successfully!';
                modalContent.innerHTML = `
                    <div class="text-center py-12">
                        <div class="mb-8">
                            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-green-400 to-emerald-500 shadow-lg mb-6">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 mb-3">Room Created Successfully!</h3>
                            <p class="text-lg text-gray-600 mb-10">Your CTF room has been added successfully. What would you like to do next?</p>
                        </div>
                        <div class="flex items-center justify-center gap-4">
                            <button 
                                onclick="closeModal('create-content-modal'); window.location.reload();" 
                                class="px-8 py-3 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                See the List
                            </button>
                            <button 
                                onclick="closeModal('create-content-modal'); openCreateModal('room');" 
                                class="px-8 py-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-400 shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Create Another
                            </button>
                        </div>
                    </div>
                `;
            }
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            if (modalId === 'create-content-modal') {
                document.getElementById('create-modal-content').innerHTML = '<div class="flex items-center justify-center py-12"><div class="text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-2 text-gray-600">{{ __('app.admin.categories.loading_form') }}</p></div></div>';
            }
            if (modalId === 'edit-content-modal') {
                document.getElementById('edit-modal-content').innerHTML = '<div class="flex items-center justify-center py-12"><div class="text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-2 text-gray-600">{{ __('app.admin.categories.loading_form') }}</p></div></div>';
            }
            if (modalId === 'remove-item-modal') {
                // Clear form reference
                const modal = document.getElementById(modalId);
                if (modal && window.Alpine && modal.__x) {
                    modal.__x.$data.form = null;
                }
                window.pendingRemoveForm = null;
            }
        }
        
        function openRemoveItemModal(form) {
            const modal = document.getElementById('remove-item-modal');
            if (modal) {
                // Store form reference - use Alpine if available, otherwise use data attribute
                if (window.Alpine && modal.__x) {
                    modal.__x.$data.form = form;
                } else {
                    // Fallback: store form in data attribute
                    modal.dataset.formId = form.id;
                    // Store form reference globally
                    window.pendingRemoveForm = form;
                }
                modal.classList.remove('hidden');
            }
        }
        
        function openEditContentModal(type, identifier) {
            const modal = document.getElementById('edit-content-modal');
            const modalContent = document.getElementById('edit-modal-content');
            const modalTitle = document.getElementById('edit-modal-title');
            
            const typeNames = {
                'book-page': 'Book Page',
                'code-summary': 'Code Summary',
                'room': 'Room',
                'certificate': 'Certificate',
                'course': 'Course'
            };
            
            modalTitle.textContent = `Edit ${typeNames[type] || type}`;
            
            const routes = {
                'book-page': '/admin/book-pages/:id/edit',
                'code-summary': '/admin/code-summaries/:id/edit',
                'room': '/admin/rooms/:id/edit',
                'certificate': '/admin/certificates/:id/edit',
                'course': '/admin/courses/:id/edit'
            };
            
            modal.classList.remove('hidden');
            
            const navItemId = {{ $nav->id }};
            const routeUrl = routes[type].replace(':id', identifier) + (routes[type].includes('?') ? '&' : '?') + 'nav_item_id=' + navItemId + '&ajax=1';
            
            fetch(routeUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const scripts = doc.querySelectorAll('script');
                scripts.forEach(script => {
                    if (script.textContent) {
                        try {
                            eval(script.textContent);
                        } catch (e) {
                            console.error('Error executing script:', e);
                        }
                    }
                });
                
                const form = doc.querySelector('form');
                
                if (form) {
                    if (type === 'book-page') {
                        if (!window.bookPageEditData) {
                            window.bookPageEditData = {
                                sections: [],
                                selectedCategories: [],
                                defaultCategoryId: ''
                            };
                        }
                    }
                    
                    // Find the wrapper div with x-data attribute (parent of form)
                    const formWrapper = form.closest('[x-data]') || form.parentElement;
                    const originalAction = form.action;
                    
                    // Clone the wrapper (which includes the form) or just the form if no wrapper found
                    const contentToClone = formWrapper && formWrapper !== form ? formWrapper : form;
                    const contentClone = contentToClone.cloneNode(true);
                    const contentScripts = contentClone.querySelectorAll('script');
                    contentScripts.forEach(s => s.remove());
                    
                    // Remove breadcrumbs, headers, and back buttons
                    const breadcrumbs = contentClone.querySelector('[class*="breadcrumb"], [class*="Breadcrumb"]');
                    if (breadcrumbs) breadcrumbs.remove();
                    
                    const header = contentClone.querySelector('h1, h2, [class*="header"], [class*="Header"]');
                    if (header && header.textContent.includes('Edit')) {
                        header.remove();
                    }
                    
                    const backButton = contentClone.querySelector('a[href*="index"], a[href*="back"]');
                    if (backButton) backButton.remove();
                    
                    // Insert HTML into modal
                    modalContent.innerHTML = contentClone.outerHTML;
                    
                    // Find the form again after insertion
                    const insertedForm = modalContent.querySelector('form');
                    if (insertedForm) {
                        insertedForm.action = originalAction;
                        insertedForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            const formData = new FormData(insertedForm);
                            formData.append('_method', 'PUT');
                            
                            fetch(originalAction, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(async response => {
                                const contentType = response.headers.get('content-type');
                                if (response.ok) {
                                    if (contentType && contentType.includes('application/json')) {
                                        const data = await response.json();
                                        if (data.success) {
                                            closeModal('edit-content-modal');
                                            window.location.reload();
                                        } else {
                                            alert('Error: ' + (data.message || 'Failed to update'));
                                        }
                                    } else {
                                        // HTML response - reload page
                                        window.location.reload();
                                    }
                                } else {
                                    // Handle error response
                                    if (contentType && contentType.includes('application/json')) {
                                        const errorData = await response.json();
                                        if (errorData.errors) {
                                            const errorMessages = Object.values(errorData.errors).flat().join('\n');
                                            alert('Validation errors:\n' + errorMessages);
                                        } else {
                                            alert('Error: ' + (errorData.message || 'Failed to update'));
                                        }
                                    } else {
                                        const text = await response.text();
                                        console.error('Error response HTML:', text.substring(0, 200));
                                        alert('Error updating item. Please try again.');
                                    }
                                }
                            })
                        });
                    }
                    
                    // Now initialize Alpine.js AFTER HTML is inserted and scripts are executed
                    setTimeout(() => {
                        // Register components first
                        if (type === 'book-page' && window.registerBookPageEditComponent) {
                            try {
                                window.registerBookPageEditComponent();
                            } catch (e) {
                                console.error('Error re-registering Alpine component:', e);
                            }
                        } else if (type === 'code-summary' && window.registerCodeSummaryEditComponent) {
                            try {
                                window.registerCodeSummaryEditComponent();
                            } catch (e) {
                                console.error('Error re-registering Alpine component:', e);
                            }
                        } else if (type === 'room' && window.registerRoomEditComponent) {
                            try {
                                window.registerRoomEditComponent();
                            } catch (e) {
                                console.error('Error re-registering Alpine component:', e);
                            }
                        } else if (type === 'course' && window.registerCourseEditComponent) {
                            try {
                                window.registerCourseEditComponent();
                            } catch (e) {
                                console.error('Error re-registering Alpine component:', e);
                            }
                        } else if (type === 'certificate' && window.registerCertificateEditComponent) {
                            try {
                                window.registerCertificateEditComponent();
                            } catch (e) {
                                console.error('Error re-registering Alpine component:', e);
                            }
                        }
                        
                        // Initialize Alpine.js on the modal content
                        if (window.Alpine) {
                            const elementWithXData = modalContent.querySelector('[x-data*="courseEdit"], [x-data*="certificateEdit"], [x-data*="bookPageEdit"], [x-data*="codeSummaryEdit"], [x-data*="roomEdit"]');
                            if (elementWithXData) {
                                window.Alpine.initTree(elementWithXData);
                            } else {
                                window.Alpine.initTree(modalContent);
                            }
                        }
                    }, 200);
                } else {
                    modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600">Error loading edit form. Please try again.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600">Error loading edit form. Please try again.</p></div>';
            });
        }
        
        function openCreateModal(type) {
            const modal = document.getElementById('create-content-modal');
            const modalContent = document.getElementById('create-modal-content');
            const modalTitle = document.getElementById('create-modal-title');
            
            // Show loading state
            modalContent.innerHTML = '<div class="flex items-center justify-center py-12"><div class="text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-2 text-gray-600">{{ __('app.admin.categories.loading_form') }}</p></div></div>';
            
            const typeNames = {
                'book-page': '{{ __('app.admin.categories.book_page') }}',
                'code-summary': '{{ __('app.admin.categories.code_summary') }}',
                'room': '{{ __('app.admin.categories.room') }}',
                'certificate': '{{ __('app.admin.categories.certificate') }}',
                'course': '{{ __('app.admin.categories.course') }}'
            };
            
            modalTitle.textContent = `{{ __('app.admin.categories.create_new') }} ${typeNames[type] || type}`;
            
            const routes = {
                'book-page': '{{ route('admin.book-pages.create') }}',
                'code-summary': '{{ route('admin.code-summaries.create') }}',
                'room': '{{ route('admin.rooms.create') }}',
                'certificate': '{{ route('admin.certificates.create') }}',
                'course': '{{ route('admin.courses.create') }}'
            };
            
            modal.classList.remove('hidden');
            
            const navItemId = {{ $nav->id }};
            const routeUrl = routes[type] + (routes[type].includes('?') ? '&' : '?') + 'nav_item_id=' + navItemId;
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600">CSRF token not found. Please refresh the page.</p></div>';
                return;
            }
            
            // Debug: Log before fetch
            console.log('Fetching create form:', routeUrl);
            console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
            console.log('Cookies:', document.cookie);
            
            fetch(routeUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin', // Include cookies/session for same origin
                cache: 'no-cache'
            })
            .then(async response => {
                // Debug: Log response details
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));
                console.log('Response URL:', response.url);
                console.log('Response redirected:', response.redirected);
                
                // Check if we got a 401 Unauthorized response (JSON)
                if (response.status === 401) {
                    const contentType = response.headers.get('content-type');
                    console.log('401 response - Content-Type:', contentType);
                    if (contentType && contentType.includes('application/json')) {
                        const data = await response.json();
                        console.log('401 JSON data:', data);
                        modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600 mb-4 font-semibold text-lg">Session Expired</p><p class="text-sm text-gray-600 mb-4">' + (data.message || 'Your session has expired. Please refresh the page and log in again.') + '</p><button onclick="window.location.reload()" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Refresh Page</button></div>';
                        throw new Error(data.message || 'Session expired');
                    }
                    // If not JSON, show error in modal instead of redirecting
                    modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600 mb-4 font-semibold text-lg">Session Expired</p><p class="text-sm text-gray-600 mb-4">Your session has expired. Please refresh the page and log in again.</p><button onclick="window.location.reload()" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Refresh Page</button></div>';
                    throw new Error('Session expired');
                }
                
                // Check if we got redirected (status 302, 301, etc.) or error
                if (response.redirected || response.status === 302 || response.status === 301) {
                    modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600 mb-4 font-semibold text-lg">Session Expired</p><p class="text-sm text-gray-600 mb-4">Your session has expired. Please refresh the page and log in again.</p><button onclick="window.location.reload()" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Refresh Page</button></div>';
                    throw new Error('Redirected - likely authentication required');
                }
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                // Check if the response is a login page or error page
                // More comprehensive check for login page
                const isLoginPage = html.includes('login') && (html.includes('password') || html.includes('email') || html.includes('Login')) ||
                                   html.includes('Forgot your password') ||
                                   html.includes('Remember me') ||
                                   (html.includes('Logout') && html.includes('form') && !html.includes('book-page') && !html.includes('code-summary') && !html.includes('certificate') && !html.includes('x-data'));
                
                if (isLoginPage) {
                    // Session likely expired - show error in modal instead of redirecting
                    modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600 mb-4 font-semibold text-lg">Session Expired</p><p class="text-sm text-gray-600 mb-4">Your session has expired. Please refresh the page and log in again.</p><button onclick="window.location.reload()" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Refresh Page</button></div>';
                    throw new Error('Session expired - login page detected');
                }
                
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Check if this looks like a login page by checking for login form
                const loginForm = doc.querySelector('form[action*="login"], form input[type="password"][name="password"]');
                if (loginForm) {
                    // Don't redirect immediately - just show error and close modal
                    modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600 mb-4 font-semibold text-lg">Session Expired</p><p class="text-sm text-gray-600 mb-4">Your session has expired. Please refresh the page and log in again.</p><button onclick="window.location.reload()" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Refresh Page</button></div>';
                    throw new Error('Received login page instead of create form - session expired');
                }
                
                // First, execute all scripts to register Alpine components
                const scripts = doc.querySelectorAll('script');
                scripts.forEach(script => {
                    if (script.textContent) {
                        try {
                            eval(script.textContent);
                        } catch (e) {
                            console.error('Error executing script:', e);
                        }
                    }
                });
                
                    // For book-page, code-summary, room, and course types, ensure component is registered after scripts execute
                    if (type === 'book-page' && window.registerBookPageCreateComponent) {
                        try {
                            window.registerBookPageCreateComponent();
                        } catch (e) {
                            console.error('Error registering Alpine component:', e);
                        }
                    } else if (type === 'code-summary' && window.registerCodeSummaryCreateComponent) {
                        try {
                            window.registerCodeSummaryCreateComponent();
                        } catch (e) {
                            console.error('Error registering Alpine component:', e);
                        }
                    } else if (type === 'room' && window.registerRoomCreateComponent) {
                        try {
                            window.registerRoomCreateComponent();
                        } catch (e) {
                            console.error('Error registering Alpine component:', e);
                        }
                    } else if (type === 'course' && window.registerCourseCreateComponent) {
                        try {
                            window.registerCourseCreateComponent();
                        } catch (e) {
                            console.error('Error registering Alpine component:', e);
                        }
                    } else if (type === 'certificate' && window.registerCertificateCreateComponent) {
                        try {
                            window.registerCertificateCreateComponent();
                        } catch (e) {
                            console.error('Error registering Alpine component:', e);
                        }
                    }
                
                const form = doc.querySelector('form');
                
                // Verify this is actually a create form, not a login/error form
                if (form) {
                    // Check if form action contains login or logout
                    if (form.action && (form.action.includes('/login') || form.action.includes('/logout'))) {
                        throw new Error('Received login/logout form instead of create form');
                    }
                    
                    // For book-page, code-summary, certificate, etc., check for expected form elements
                    if (type === 'book-page' && !doc.querySelector('[x-data*="bookPageCreate"], input[name="title"], textarea[name="summary"]')) {
                        throw new Error('Form does not contain expected book-page create form elements');
                    } else if (type === 'code-summary' && !doc.querySelector('[x-data*="codeSummaryCreate"], input[name="title"], textarea[name="summary"]')) {
                        throw new Error('Form does not contain expected code-summary create form elements');
                    } else if (type === 'certificate' && !doc.querySelector('[x-data*="certificateCreate"], input[name="title"], input[name="provider"]')) {
                        throw new Error('Form does not contain expected certificate create form elements');
                    }
                }
                
                if (form) {
                    if (type === 'book-page' || type === 'code-summary' || type === 'room' || type === 'course' || type === 'certificate') {
                        // Ensure data exists
                        if (type === 'book-page' && !window.bookPageCreateData) {
                            window.bookPageCreateData = {
                                sections: [],
                                selectedCategories: []
                            };
                        } else if (type === 'code-summary' && !window.codeSummaryCreateData) {
                            window.codeSummaryCreateData = {
                                sections: [],
                                selectedCategories: []
                            };
                        } else if (type === 'room' && !window.roomCreateData) {
                            window.roomCreateData = {
                                sections: [],
                                selectedCategories: []
                            };
                        } else if (type === 'course' && !window.courseCreateData) {
                            window.courseCreateData = {
                                sections: [],
                                selectedCategories: []
                            };
                        } else if (type === 'certificate' && !window.certificateCreateData) {
                            window.certificateCreateData = {
                                sections: [],
                                selectedCategories: []
                            };
                        }
                    }
                    
                    const originalAction = form.action;
                    
                    // For book-page, code-summary, room, course, and certificate, find the parent div with x-data
                    let elementToClone = form;
                    if (type === 'book-page') {
                        const parentWithXData = form.closest('[x-data*="bookPageCreate"]');
                        if (parentWithXData) {
                            elementToClone = parentWithXData;
                        }
                    } else if (type === 'code-summary') {
                        const parentWithXData = form.closest('[x-data*="codeSummaryCreate"]');
                        if (parentWithXData) {
                            elementToClone = parentWithXData;
                        }
                    } else if (type === 'room') {
                        const parentWithXData = form.closest('[x-data*="roomCreate"]');
                        if (parentWithXData) {
                            elementToClone = parentWithXData;
                        }
                    } else if (type === 'course') {
                        const parentWithXData = form.closest('[x-data*="courseCreate"]');
                        if (parentWithXData) {
                            elementToClone = parentWithXData;
                        }
                    } else if (type === 'certificate') {
                        const parentWithXData = form.closest('[x-data*="certificateCreate"]');
                        if (parentWithXData) {
                            elementToClone = parentWithXData;
                        }
                    }
                    
                    const formClone = elementToClone.cloneNode(true);
                    // Don't remove scripts - they might contain Alpine component registration
                    // Only remove scripts that are inside the form content
                    const formScripts = formClone.querySelectorAll('script');
                    formScripts.forEach(s => {
                        // Keep scripts that register Alpine components
                        const scriptText = s.textContent || '';
                        if (!scriptText.includes('Alpine.data') && 
                            !scriptText.includes('registerBookPageCreateComponent') && 
                            !scriptText.includes('registerCodeSummaryCreateComponent') &&
                            !scriptText.includes('registerRoomCreateComponent') &&
                            !scriptText.includes('registerCourseCreateComponent') &&
                            !scriptText.includes('registerCertificateCreateComponent') &&
                            !scriptText.includes('bookPageCreateData') &&
                            !scriptText.includes('codeSummaryCreateData') &&
                            !scriptText.includes('roomCreateData') &&
                            !scriptText.includes('courseCreateData') &&
                            !scriptText.includes('certificateCreateData')) {
                            s.remove();
                        }
                    });
                    
                    modalContent.innerHTML = formClone.outerHTML;
                    
                    // If we only cloned the form (not the wrapper), add x-data to modalContent
                    if ((type === 'book-page' || type === 'code-summary' || type === 'room' || type === 'course' || type === 'certificate') && elementToClone === form) {
                        // Find the form we just inserted
                        const insertedForm = modalContent.querySelector('form');
                        if (insertedForm) {
                            // Wrap it and any siblings (like preview) in a div with x-data
                            const wrapper = document.createElement('div');
                            let xDataValue = '';
                            if (type === 'book-page') {
                                xDataValue = 'bookPageCreate()';
                            } else if (type === 'code-summary') {
                                xDataValue = 'codeSummaryCreate()';
                            } else if (type === 'room') {
                                xDataValue = 'roomCreate()';
                            } else if (type === 'course') {
                                xDataValue = 'courseCreate()';
                            } else if (type === 'certificate') {
                                xDataValue = 'certificateCreate()';
                            }
                            wrapper.setAttribute('x-data', xDataValue);
                            wrapper.className = 'grid grid-cols-1 lg:grid-cols-3 gap-8';
                            
                            // Move form and any following siblings into wrapper
                            const parent = insertedForm.parentNode;
                            
                            // Insert wrapper before form
                            parent.insertBefore(wrapper, insertedForm);
                            
                            // Move form and any preview divs into wrapper
                            let nextSibling = insertedForm;
                            while (nextSibling && (nextSibling.tagName === 'FORM' || nextSibling.tagName === 'DIV')) {
                                const toMove = nextSibling;
                                nextSibling = nextSibling.nextElementSibling;
                                wrapper.appendChild(toMove);
                            }
                        }
                    }
                    
                    // Execute any remaining scripts after inserting into DOM
                    const insertedScripts = modalContent.querySelectorAll('script');
                    insertedScripts.forEach(script => {
                        if (script.textContent) {
                            try {
                                eval(script.textContent);
                            } catch (e) {
                                console.error('Error executing inserted script:', e);
                            }
                        }
                    });
                    
                    // Re-register Alpine component after DOM insertion (for book-page, code-summary, and room)
                    if (type === 'book-page') {
                        if (window.registerBookPageCreateComponent) {
                            try {
                                window.registerBookPageCreateComponent();
                            } catch (e) {
                                console.error('Error re-registering Alpine component:', e);
                            }
                        }
                        if (window.registerBookPageTranslationComponent) {
                            try {
                                window.registerBookPageTranslationComponent();
                            } catch (e) {
                                console.error('Error re-registering translation component:', e);
                            }
                        }
                    } else if (type === 'code-summary') {
                        if (window.registerCodeSummaryCreateComponent) {
                            try {
                                window.registerCodeSummaryCreateComponent();
                            } catch (e) {
                                console.error('Error re-registering Alpine component:', e);
                            }
                        }
                        if (window.registerCodeSummaryTranslationComponent) {
                            try {
                                window.registerCodeSummaryTranslationComponent();
                            } catch (e) {
                                console.error('Error re-registering translation component:', e);
                            }
                        }
                    } else if (type === 'room') {
                        if (window.registerRoomCreateComponent) {
                            try {
                                window.registerRoomCreateComponent();
                            } catch (e) {
                                console.error('Error re-registering Alpine component:', e);
                            }
                        }
                        if (window.registerRoomTranslationComponent) {
                            try {
                                window.registerRoomTranslationComponent();
                            } catch (e) {
                                console.error('Error re-registering translation component:', e);
                            }
                        }
                    } else if (type === 'course' && window.registerCourseCreateComponent) {
                        try {
                            window.registerCourseCreateComponent();
                        } catch (e) {
                            console.error('Error re-registering Alpine component:', e);
                        }
                    } else if (type === 'certificate' && window.registerCertificateCreateComponent) {
                        try {
                            window.registerCertificateCreateComponent();
                        } catch (e) {
                            console.error('Error re-registering Alpine component:', e);
                        }
                    }
                    
                    const insertedForm = modalContent.querySelector('form');
                    if (insertedForm) {
                        insertedForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            submitCreateForm(insertedForm, originalAction, type);
                        });
                    }
                    
                    // Initialize Alpine.js after scripts are executed and DOM is updated
                    // Wait a bit longer to ensure Alpine component registration completes
                    setTimeout(() => {
                        if (window.Alpine) {
                            // Ensure Alpine component is registered before initializing
                            if (type === 'book-page') {
                                // Make sure components are registered
                                if (window.registerBookPageCreateComponent) {
                                    try {
                                        window.registerBookPageCreateComponent();
                                    } catch (e) {
                                        console.error('Error ensuring Alpine component registration:', e);
                                    }
                                }
                                if (window.registerBookPageTranslationComponent) {
                                    try {
                                        window.registerBookPageTranslationComponent();
                                    } catch (e) {
                                        console.error('Error ensuring translation component registration:', e);
                                    }
                                }
                                // Find the element with x-data and initialize Alpine on it
                                const elementWithXData = modalContent.querySelector('[x-data*="bookPageCreate"]');
                                if (elementWithXData) {
                                    window.Alpine.initTree(elementWithXData);
                                } else {
                                    // Fallback: initialize on modalContent
                                    window.Alpine.initTree(modalContent);
                                }
                            } else if (type === 'code-summary') {
                                // Make sure components are registered
                                if (window.registerCodeSummaryCreateComponent) {
                                    try {
                                        window.registerCodeSummaryCreateComponent();
                                    } catch (e) {
                                        console.error('Error ensuring Alpine component registration:', e);
                                    }
                                }
                                if (window.registerCodeSummaryTranslationComponent) {
                                    try {
                                        window.registerCodeSummaryTranslationComponent();
                                    } catch (e) {
                                        console.error('Error ensuring translation component registration:', e);
                                    }
                                }
                                // Find the element with x-data and initialize Alpine on it
                                const elementWithXData = modalContent.querySelector('[x-data*="codeSummaryCreate"]');
                                if (elementWithXData) {
                                    window.Alpine.initTree(elementWithXData);
                                } else {
                                    // Fallback: initialize on modalContent
                                    window.Alpine.initTree(modalContent);
                                }
                            } else if (type === 'room') {
                                // Make sure component is registered
                                if (window.registerRoomCreateComponent) {
                                    try {
                                        window.registerRoomCreateComponent();
                                    } catch (e) {
                                        console.error('Error ensuring Alpine component registration:', e);
                                    }
                                }
                                if (window.registerRoomTranslationComponent) {
                                    try {
                                        window.registerRoomTranslationComponent();
                                    } catch (e) {
                                        console.error('Error ensuring translation component registration:', e);
                                    }
                                }
                                // Find the element with x-data and initialize Alpine on it
                                const elementWithXData = modalContent.querySelector('[x-data*="roomCreate"]');
                                if (elementWithXData) {
                                    window.Alpine.initTree(elementWithXData);
                                } else {
                                    // Fallback: initialize on modalContent
                                    window.Alpine.initTree(modalContent);
                                }
                            } else if (type === 'course') {
                                // Make sure component is registered
                                if (window.registerCourseCreateComponent) {
                                    try {
                                        window.registerCourseCreateComponent();
                                    } catch (e) {
                                        console.error('Error ensuring Alpine component registration:', e);
                                    }
                                }
                                // Find the element with x-data and initialize Alpine on it
                                const elementWithXData = modalContent.querySelector('[x-data*="courseCreate"]');
                                if (elementWithXData) {
                                    window.Alpine.initTree(elementWithXData);
                                } else {
                                    // Fallback: initialize on modalContent
                                    window.Alpine.initTree(modalContent);
                                }
                            } else if (type === 'certificate') {
                                // Make sure component is registered
                                if (window.registerCertificateCreateComponent) {
                                    try {
                                        window.registerCertificateCreateComponent();
                                    } catch (e) {
                                        console.error('Error ensuring Alpine component registration:', e);
                                    }
                                }
                                // Find the element with x-data and initialize Alpine on it
                                const elementWithXData = modalContent.querySelector('[x-data*="certificateCreate"]');
                                if (elementWithXData) {
                                    window.Alpine.initTree(elementWithXData);
                                } else {
                                    // Fallback: initialize on modalContent
                                    window.Alpine.initTree(modalContent);
                                }
                            } else {
                                window.Alpine.initTree(modalContent);
                            }
                        }
                    }, 500);
                } else {
                    modalContent.innerHTML = '<div class="text-center py-12"><p class="text-red-600 mb-4">{{ __('app.admin.categories.error_loading_form') }}</p><p class="text-sm text-gray-600">No form found in the response. Please try refreshing the page.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading form:', error);
                const errorMessage = error.message && (error.message.includes('Session expired') || error.message.includes('authentication') || error.message.includes('Redirected')) 
                    ? '<div class="text-center py-12"><p class="text-red-600 mb-4 font-semibold text-lg">Session Expired</p><p class="text-sm text-gray-600 mb-4">Your session has expired. Please log in again.</p><a href="{{ route("login") }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Go to Login Page</a></div>'
                    : '<div class="text-center py-12"><p class="text-red-600 mb-4">{{ __('app.admin.categories.error_loading_form') }}</p><p class="text-sm text-gray-600 mb-4">' + (error.message || 'An unexpected error occurred') + '</p><button onclick="closeModal(\'create-content-modal\')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">Close</button></div>';
                modalContent.innerHTML = errorMessage;
            });
        }
        
        function submitCreateForm(form, action, type) {
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.textContent : 'Submit';
            
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = '{{ __('app.admin.categories.creating') }}';
            }
            
            fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                if (response.ok) {
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // If response is HTML, it might be a redirect or error page
                        const text = await response.text();
                        console.error('Expected JSON but got HTML:', text.substring(0, 200));
                        throw new Error('Server returned HTML instead of JSON');
                    }
                } else {
                    // Handle error response
                    if (contentType && contentType.includes('application/json')) {
                        const errorData = await response.json();
                        // Display validation errors if present
                        if (errorData.errors) {
                            const errorMessages = Object.values(errorData.errors).flat().join('\n');
                            alert('Validation errors:\n' + errorMessages);
                        } else {
                            alert('Error: ' + (errorData.message || 'Failed to create'));
                        }
                        return Promise.reject(errorData);
                    } else {
                        // Error page returned as HTML
                        const text = await response.text();
                        console.error('Error response HTML:', text.substring(0, 200));
                        throw new Error('Server returned error page');
                    }
                }
            })
            .then(data => {
                if (data.success) {
                    // Show success popup for rooms and courses
                    if (type === 'room') {
                        showRoomSuccessModal();
                    } else if (type === 'course') {
                        showCourseSuccessModal();
                    } else {
                        closeModal('create-content-modal');
                        window.location.reload();
                    }
                } else {
                    alert('Error: ' + (data.message || 'Failed to create'));
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Error already handled in the response processing above
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            });
        }
        
        // Handle create item form submission
        function handleCreateItemFormSubmit(form) {
            const formData = new FormData(form);
            const submitButton = document.getElementById('create-item-submit-btn');
            const originalText = submitButton ? submitButton.textContent : 'Create';
            
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = '{{ __('app.admin.categories.creating') }}';
            }
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                if (response.ok) {
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        // If response is HTML, it might be a redirect or error page
                        const text = await response.text();
                        console.error('Expected JSON but got HTML:', text.substring(0, 200));
                        throw new Error('Server returned HTML instead of JSON');
                    }
                } else {
                    // Handle error response
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(err => Promise.reject(err));
                    } else {
                        // Error page returned as HTML
                        const text = await response.text();
                        console.error('Error response HTML:', text.substring(0, 200));
                        throw new Error('Server returned error page');
                    }
                }
            })
            .then(data => {
                if (data.success || data.message) {
                    // Show success message
                    showCreateItemSuccess();
                } else {
                    alert('Error: ' + (data.message || 'Failed to create'));
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating section: ' + (error.message || 'Please try again.'));
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            });
        }
        
        function showCreateItemSuccess() {
            const successDiv = document.getElementById('create-item-success');
            const form = document.getElementById('create-item-form');
            if (successDiv && form) {
                form.style.display = 'none';
                successDiv.classList.remove('hidden');
            }
        }
        
        function resetCreateItemForm() {
            const successDiv = document.getElementById('create-item-success');
            const form = document.getElementById('create-item-form');
            const submitButton = document.getElementById('create-item-submit-btn');
            
            if (successDiv) {
                successDiv.classList.add('hidden');
            }
            if (form) {
                form.style.display = 'flex';
                form.reset();
                // Reset file input
                const fileInput = form.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.value = '';
                }
            }
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Create';
            }
            
            // Reload page to show the new section in the list
            window.location.reload();
        }
        
        function closeCreateItemModal(reload = false) {
            const modal = document.getElementById('create-item-modal');
            const successDiv = document.getElementById('create-item-success');
            const form = document.getElementById('create-item-form');
            
            if (modal) {
                modal.classList.add('hidden');
            }
            if (successDiv) {
                successDiv.classList.add('hidden');
            }
            if (form) {
                form.style.display = 'flex';
                form.reset();
                const fileInput = form.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.value = '';
                }
            }
            
            if (reload) {
                window.location.reload();
            }
        }
        
        // Handle content type selection in add content modal
        document.addEventListener('DOMContentLoaded', function() {
            // Set up create item form submission
            const createItemForm = document.getElementById('create-item-form');
            if (createItemForm) {
                createItemForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    handleCreateItemFormSubmit(this);
                });
            }
            
            const contentTypeSelect = document.getElementById('content-type-select');
            if (contentTypeSelect) {
                contentTypeSelect.addEventListener('change', function() {
                    const selectedType = this.value;
                    ['book-pages-select', 'code-summaries-select', 'rooms-select', 'certificates-select', 'courses-select'].forEach(id => {
                        document.getElementById(id).classList.add('hidden');
                    });
                    
                    if (selectedType === 'App\\Models\\BookPage') {
                        document.getElementById('book-pages-select').classList.remove('hidden');
                    } else if (selectedType === 'App\\Models\\CodeSummary') {
                        document.getElementById('code-summaries-select').classList.remove('hidden');
                    } else if (selectedType === 'App\\Models\\Room') {
                        document.getElementById('rooms-select').classList.remove('hidden');
                    } else if (selectedType === 'App\\Models\\Certificate') {
                        document.getElementById('certificates-select').classList.remove('hidden');
                    } else if (selectedType === 'App\\Models\\Course') {
                        document.getElementById('courses-select').classList.remove('hidden');
                    }
                });
            }
            
            // Close modals when clicking outside
            document.getElementById('add-content-modal')?.addEventListener('click', function(e) {
                if (e.target === this) closeModal('add-content-modal');
            });
            document.getElementById('create-content-modal')?.addEventListener('click', function(e) {
                if (e.target === this) closeModal('create-content-modal');
            });
        });
    </script>
    
    {{-- Add Content Modal (Dynamic - populated via JavaScript) --}}
    <div id="add-content-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="add-content-modal-title">{{ __('app.admin.categories.add_content_to_section') }}</h3>
                <button onclick="closeModal('add-content-modal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="add-content-form" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.categories.content_type') }}</label>
                            <select name="content_type" id="content-type-select" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500" required>
                                <option value="">{{ __('app.admin.categories.select_content_type') }}</option>
                                <option value="App\Models\BookPage">{{ __('app.admin.categories.book_page') }}</option>
                                <option value="App\Models\CodeSummary">{{ __('app.admin.categories.code_summary') }}</option>
                                <option value="App\Models\Room">{{ __('app.admin.categories.room') }}</option>
                                <option value="App\Models\Certificate">{{ __('app.admin.categories.certificate') }}</option>
                                <option value="App\Models\Course">{{ __('app.admin.categories.course') }}</option>
                            </select>
                        </div>

                        {{-- Book Pages --}}
                        <div id="book-pages-select" class="hidden">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Select Book Page(s) - Hold Ctrl/Cmd to select multiple</label>
                                <button type="button" onclick="openCreateModal('book-page')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create New Book Page
                                </button>
                            </div>
                            <select name="content_ids[]" id="book-page-select" multiple class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500" size="8">
                                @foreach($allBookPages as $bookPage)
                                    <option value="{{ $bookPage->id }}">{{ $bookPage->getTranslated('title') ?: $bookPage->slug }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple items</p>
                        </div>

                        {{-- Code Summaries --}}
                        <div id="code-summaries-select" class="hidden">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Select Code Summary(ies) - Hold Ctrl/Cmd to select multiple</label>
                                <button type="button" onclick="openCreateModal('code-summary')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create New Code Summary
                                </button>
                            </div>
                            <select name="content_ids[]" id="code-summary-select" multiple class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500" size="8">
                                @foreach($allCodeSummaries as $codeSummary)
                                    <option value="{{ $codeSummary->id }}">{{ $codeSummary->getTranslated('title') ?: $codeSummary->slug }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple items</p>
                        </div>

                        {{-- Rooms --}}
                        <div id="rooms-select" class="hidden">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Select Room(s) - Hold Ctrl/Cmd to select multiple</label>
                                <button type="button" onclick="openCreateModal('room')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create New Room
                                </button>
                            </div>
                            <select name="content_ids[]" id="room-select" multiple class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500" size="8">
                                @foreach($allRooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->getTranslated('title') ?: $room->slug }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple items</p>
                        </div>

                        {{-- Certificates --}}
                        <div id="certificates-select" class="hidden">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Select Certificate(s) - Hold Ctrl/Cmd to select multiple</label>
                                <button type="button" onclick="openCreateModal('certificate')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-md transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create New Certificate
                                </button>
                            </div>
                            <select name="content_ids[]" id="certificate-select" multiple class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500" size="8">
                                @foreach($allCertificates as $certificate)
                                    <option value="{{ $certificate->id }}">{{ $certificate->getTranslated('title') ?: $certificate->slug }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple items</p>
                        </div>

                        {{-- Courses --}}
                        <div id="courses-select" class="hidden">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">Select Course(s) - Hold Ctrl/Cmd to select multiple</label>
                                <button type="button" onclick="openCreateModal('course')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-md transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create New Course
                                </button>
                            </div>
                            <select name="content_ids[]" id="course-select" multiple class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500" size="8">
                                @foreach($allCourses as $course)
                                    <option value="{{ $course->id }}">{{ $course->getTranslated('title') ?: $course->slug }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple items</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.categories.position_optional') }}</label>
                            <input type="number" name="position" value="0" min="0" class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        </div>
                    </div>
                </div>
                
                <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('add-content-modal')" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                        {{ __('app.common.cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium bg-teal-600 hover:bg-teal-700 text-white rounded-md transition-colors">
                        {{ __('app.admin.categories.add_to_section') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Create Content Modal --}}
    <div id="create-content-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] flex flex-col">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="create-modal-title">{{ __('app.admin.categories.create_new_content') }}</h3>
                <button onclick="closeModal('create-content-modal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6" id="create-modal-content">
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">{{ __('app.admin.categories.loading_form') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Content Modal --}}
    <div id="edit-content-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] flex flex-col">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="edit-modal-title">Edit Content</h3>
                <button onclick="closeModal('edit-content-modal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6" id="edit-modal-content">
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">{{ __('app.admin.categories.loading_form') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Remove Item Confirmation Modal --}}
    <div id="remove-item-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data="{ form: null }" x-init="if(window.pendingRemoveForm) { form = window.pendingRemoveForm; window.pendingRemoveForm = null; }">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full" x-transition>
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Remove Item from Section?</h3>
                <p class="text-sm text-gray-600 text-center mb-6">
                    This will remove the item from this section. The item itself will not be deleted, only the link to this section.
                </p>
                <div class="flex items-center justify-end gap-3">
                    <button 
                        type="button" 
                        onclick="closeModal('remove-item-modal')" 
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        @click="if(form) { form.submit(); } else if(window.pendingRemoveForm) { window.pendingRemoveForm.submit(); window.pendingRemoveForm = null; }"
                        class="px-6 py-2.5 text-sm font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all shadow-md hover:shadow-lg"
                    >
                        Remove Item
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

