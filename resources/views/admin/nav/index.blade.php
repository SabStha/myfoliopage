@extends('layouts.app')
@section('title', __('app.admin.nav.title'))
@section('content')
    <div x-data="{ activeTab: 'nav' }">
        {{-- Hero Header --}}
        <div class="mb-4 sm:mb-8">
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-1 sm:mb-2">{{ __('app.admin.nav.title') }}</h1>
                        <p class="text-indigo-100 text-sm sm:text-base lg:text-lg">{{ __('app.admin.nav.subtitle') }}</p>
                    </div>
                    <div class="hidden md:block flex-shrink-0 ml-4">
                        <div class="w-16 h-16 lg:w-24 lg:h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <svg class="w-8 h-8 lg:w-12 lg:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="mb-4 sm:mb-6">
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="-mb-px flex space-x-4 sm:space-x-8 min-w-max sm:min-w-0" aria-label="Tabs">
                    <button 
                        @click="activeTab = 'nav'"
                        :class="activeTab === 'nav' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 flex items-center gap-1 sm:gap-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                        </svg>
                        <span class="hidden sm:inline">{{ __('app.admin.nav.sidebar_navigation') }}</span>
                        <span class="sm:hidden">{{ __('app.admin.nav.nav') }}</span>
                        <span class="ml-1 sm:ml-2 py-0.5 px-1.5 sm:px-2 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">{{ $items->count() }}</span>
                    </button>
                    <button 
                        @click="activeTab = 'sections'"
                        :class="activeTab === 'sections' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 flex items-center gap-1 sm:gap-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span class="hidden sm:inline">{{ __('app.admin.nav.home_page_sections') }}</span>
                        <span class="sm:hidden">{{ __('app.admin.nav.sections') }}</span>
                        <span class="ml-1 sm:ml-2 py-0.5 px-1.5 sm:px-2 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">{{ $sections->count() }}</span>
                    </button>
                </nav>
            </div>
        </div>

        {{-- Success Message --}}
        @if(session('status'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-800 font-medium">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        {{-- Tab Content: Navigation Items --}}
        <div x-show="activeTab === 'nav'" x-transition class="space-y-4 sm:space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 sm:mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ __('app.admin.nav.sidebar_navigation_items') }}</h2>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">{{ __('app.admin.nav.manage_items_sidebar') }}</p>
                </div>
                <a href="{{ route('admin.nav.create') }}" class="inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('app.admin.nav.add_navigation_item') }}</span>
                    <span class="sm:hidden">{{ __('app.admin.nav.add_item') }}</span>
                </a>
            </div>

            @if($items->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($items as $item)
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg">
                                            {!! $item->icon_svg ?? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6l2 2h10v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>' !!}
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ $item->getTranslated('label') ?: 'Untitled' }}</h3>
                                            <p class="text-xs text-gray-500">Position: {{ $item->position }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->visible ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $item->visible ? __('app.admin.nav.visible') : __('app.admin.nav.hidden') }}
                                    </span>
                                </div>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                        <span class="font-medium">{{ $item->links_count ?? 0 }}</span>
                                        <span>{{ __('app.admin.nav.sub_items') }}</span>
                                    </div>
                                    @if($item->route)
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                            <span class="font-mono text-xs">{{ $item->route }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 pt-4 border-t border-gray-200">
                                    <a href="{{ route('admin.nav.links.index', $item) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg font-medium transition-colors text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                                        </svg>
                                        {{ __('app.admin.nav.manage') }}
                                    </a>
                                    <a href="{{ route('admin.nav.edit', $item) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 hover:bg-gray-100 rounded-lg font-medium transition-colors text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('app.admin.nav.edit') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.admin.nav.no_navigation_items') }}</h3>
                    <p class="text-gray-600 mb-6">{{ __('app.admin.nav.get_started') }}</p>
                    <a href="{{ route('admin.nav.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.admin.nav.add_navigation_item') }}
                    </a>
                </div>
            @endif
        </div>

        {{-- Tab Content: Home Page Sections --}}
        <div x-show="activeTab === 'sections'" x-transition class="space-y-4 sm:space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 sm:mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ __('app.admin.nav.home_page_sections') }}</h2>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">{{ __('app.admin.nav.configure_sections') }}</p>
                </div>
                <button onclick="openHomePageSectionModal(null)" class="inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('app.admin.nav.add_section') }}</span>
                    <span class="sm:hidden">{{ __('app.admin.nav.add') }}</span>
                </button>
            </div>

            @if($sections->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($sections as $section)
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        @if($section->navItem)
                                            @php
                                                $label = $section->navItem->getTranslated('label', app()->getLocale()) ?: '';
                                                if (!is_string($label)) {
                                                    $labelArray = is_array($section->navItem->label) ? $section->navItem->label : [];
                                                    $label = $labelArray[app()->getLocale()] ?? $labelArray['en'] ?? $labelArray['ja'] ?? '';
                                                }
                                            @endphp
                                            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ ucfirst($label) }}</h3>
                                        @else
                                            <h3 class="text-lg font-bold text-gray-900 mb-1">N/A</h3>
                                        @endif
                                        @if($section->getTranslated('title'))
                                            <p class="text-sm text-gray-600">{{ $section->getTranslated('title') }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.nav.position') }}: {{ $section->position }}</p>
                                    </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $section->enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $section->enabled ? __('app.admin.nav.enabled') : __('app.admin.nav.disabled') }}
                                    </span>
                                </div>

                                <div class="space-y-3 mb-4">
                                    @php
                                        $selectedIds = $section->selected_nav_link_ids;
                                        if ($selectedIds === null) {
                                            $allNavLinksCount = \App\Models\NavLink::where('nav_item_id', $section->nav_item_id)->count();
                                            $subsectionsCount = $allNavLinksCount;
                                            $subsectionsText = $allNavLinksCount > 0 ? "$subsectionsCount (all)" : "0";
                                        } else {
                                            $subsectionsCount = is_array($selectedIds) ? count($selectedIds) : 0;
                                            $subsectionsText = (string)$subsectionsCount;
                                        }
                                    @endphp
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        <span class="font-medium">{{ $subsectionsText }}</span>
                                        <span>{{ __('app.admin.nav.subsections') }}</span>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        @if($section->animation_style === 'grid_editorial_collage')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Grid Collage</span>
                                        @elseif($section->animation_style === 'list_alternating_cards')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">List Cards</span>
                                        @elseif($section->animation_style === 'carousel_scroll_left')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Carousel ←</span>
                                        @elseif($section->animation_style === 'carousel_scroll_right')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Carousel →</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Default</span>
                                        @endif
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                            {{ ucfirst($section->text_alignment) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-4 border-t border-gray-200">
                                    @if($section->navItem)
                                        <a href="{{ route('admin.nav.links.index', $section->navItem) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg font-medium transition-colors text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                                            </svg>
                                            {{ __('app.admin.nav.manage') }}
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.home-page-sections.toggle-enabled', $section) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 {{ $section->enabled ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 'bg-green-50 text-green-700 hover:bg-green-100' }} rounded-lg font-medium transition-colors text-sm" title="{{ $section->enabled ? 'Disable section' : 'Enable section' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($section->enabled)
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                @endif
                                            </svg>
                                        </button>
                                    </form>
                                    <button onclick="openHomePageSectionModal({{ $section->id }})" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 hover:bg-gray-100 rounded-lg font-medium transition-colors text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('app.admin.nav.edit') }}
                                    </button>
                                    <form action="{{ route('admin.home-page-sections.destroy', $section) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this section?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg font-medium transition-colors text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('app.admin.nav.no_home_page_sections') }}</h3>
                    <p class="text-gray-600 mb-6">{{ __('app.admin.nav.create_first_section') }}</p>
                    <button onclick="openHomePageSectionModal(null)" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.admin.nav.add_section') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    <x-home-page-section-modal />

    <script>
    function openHomePageSectionModal(sectionId) {
        if (sectionId) {
            window.dispatchEvent(new CustomEvent('load-home-page-section', { 
                detail: { id: sectionId } 
            }));
        }
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'home-page-section-modal' }));
    }
    </script>
@endsection
