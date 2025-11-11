@extends('layouts.app')
@section('title', 'Home Page Sections')
@section('content')
<div class="max-w-6xl mx-auto p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Home Page Sections</h1>
            <p class="text-gray-600 mt-1">Manage which sections appear on your home page and their order</p>
        </div>
        <button onclick="openHomePageSectionModal(null)" class="px-4 py-2 bg-[#ffb400] text-gray-900 font-semibold rounded-lg hover:bg-[#e6a200] transition-colors">
            Add Section
        </button>
    </div>

    @if(session('status'))
    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
        <p class="text-green-800 font-medium">{{ session('status') }}</p>
    </div>
    @endif

    @if($sections->count() > 0)
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subsections</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layout</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($sections as $section)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                @if($section->navItem)
                                    <a href="{{ route('admin.nav.links.index', $section->navItem) }}" 
                                       class="text-sm font-semibold text-gray-900 hover:text-[#ffb400] transition-colors">
                                        {{ ucfirst($section->navItem->label) }}
                                    </a>
                                @else
                                    <span class="text-sm font-semibold text-gray-900">N/A</span>
                                @endif
                                @if($section->title)
                                    <span class="text-xs text-gray-500">{{ $section->title }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                            @php
                                $selectedIds = $section->selected_nav_link_ids;
                                if ($selectedIds === null) {
                                    // null means "show all" - count all navLinks for this navItem
                                    $allNavLinksCount = \App\Models\NavLink::where('nav_item_id', $section->nav_item_id)->count();
                                    $subsectionsCount = $allNavLinksCount;
                                    $subsectionsText = $allNavLinksCount > 0 ? "$subsectionsCount (all)" : "0";
                                } else {
                                    $subsectionsCount = is_array($selectedIds) ? count($selectedIds) : 0;
                                    $subsectionsText = (string)$subsectionsCount;
                                }
                            @endphp
                            <span class="font-medium">{{ $subsectionsText }}</span> subsections
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
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
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($section->enabled)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-600" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-600" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Disabled
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-3">
                                <button onclick="openHomePageSectionModal({{ $section->id }})" 
                                   class="inline-flex items-center px-3 py-1.5 border border-blue-300 rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 hover:text-blue-800 transition-colors">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </button>
                                <form action="{{ route('admin.home-page-sections.destroy', $section) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this section?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded-md text-red-700 bg-red-50 hover:bg-red-100 hover:text-red-800 transition-colors">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow border border-gray-200 p-8 text-center">
        <p class="text-gray-600">No sections configured yet. <button onclick="openHomePageSectionModal(null)" class="text-[#ffb400] hover:underline">Create your first section</button></p>
    </div>
    @endif
</div>

<x-home-page-section-modal />

<script>
function openHomePageSectionModal(sectionId) {
    // Dispatch event to load section data if editing
    if (sectionId) {
        window.dispatchEvent(new CustomEvent('load-home-page-section', { 
            detail: { id: sectionId } 
        }));
    }
    
    // Dispatch event to open modal
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'home-page-section-modal' }));
}
</script>
@endsection

