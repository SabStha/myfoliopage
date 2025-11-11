@extends('layouts.app')
@section('title', 'Edit Home Page Section')
@section('content')
<div class="max-w-4xl mx-auto p-6" x-data="sectionForm()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Home Page Section</h1>
        <p class="text-gray-600 mt-1">Update section configuration and display settings</p>
    </div>

    <form method="POST" action="{{ route('admin.home-page-sections.update', $homePageSection) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">Section Configuration</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Section (NavItem) *</label>
                    <select name="nav_item_id" x-model="selectedNavItem" @change="loadNavLinks()" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#ffb400] focus:border-[#ffb400]">
                        <option value="">Choose a section...</option>
                        @foreach($navItems as $navItem)
                            <option value="{{ $navItem->id }}" {{ old('nav_item_id', $homePageSection->nav_item_id) == $navItem->id ? 'selected' : '' }}>{{ $navItem->getTranslated('label') ?: 'Untitled' }}</option>
                        @endforeach
                    </select>
                    @error('nav_item_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position *</label>
                        <input type="number" name="position" value="{{ old('position', $homePageSection->position) }}" required min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#ffb400] focus:border-[#ffb400]">
                        @error('position')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Text Alignment *</label>
                        <select name="text_alignment" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#ffb400] focus:border-[#ffb400]">
                            <option value="left" {{ old('text_alignment', $homePageSection->text_alignment) === 'left' ? 'selected' : '' }}>Left</option>
                            <option value="right" {{ old('text_alignment', $homePageSection->text_alignment) === 'right' ? 'selected' : '' }}>Right</option>
                        </select>
                        @error('text_alignment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-dual-language-input 
                            name="title" 
                            label="{{ __('app.admin.home_page_section.title_optional') }}" 
                            :value="$homePageSection->getTranslations('title')"
                            :placeholder="__('app.admin.home_page_section.title_placeholder')"
                        />
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-dual-language-input 
                            name="subtitle" 
                            label="{{ __('app.admin.home_page_section.subtitle_optional') }}" 
                            :value="$homePageSection->getTranslations('subtitle')"
                            :placeholder="__('app.admin.home_page_section.subtitle_placeholder')"
                        />
                        @error('subtitle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" {{ old('enabled', $homePageSection->enabled) ? 'checked' : '' }} class="rounded border-gray-300 text-[#ffb400] focus:ring-[#ffb400]">
                        <span class="text-sm font-medium text-gray-700">Enabled</span>
                    </label>
                </div>
            </div>
        </x-card>

        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">Select Subsections (NavLinks)</h2>
            <p class="text-sm text-gray-600 mb-4">All subsections are shown by default. Uncheck subsections to hide them from the home page.</p>
            
            <div x-show="navLinks.length > 0" class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-2">All subsections are checked by default (all will be shown). Uncheck to hide specific subsections.</p>
                <template x-for="link in navLinks" :key="link.id">
                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input type="checkbox" :name="'selected_nav_link_ids[]'" :value="link.id" :checked="selectedNavLinkIds.some(id => Number(id) === Number(link.id))" @change="toggleNavLink(link.id)" class="rounded border-gray-300 text-[#ffb400] focus:ring-[#ffb400]">
                        <span class="text-sm text-gray-700" x-text="link.title_translated || 'Untitled'"></span>
                    </label>
                </template>
            </div>
            <div x-show="navLinks.length === 0 && selectedNavItem" class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                <p class="text-sm text-gray-600">No subsections available for this section.</p>
                <p class="text-xs text-gray-500 mt-1">Add NavLinks in <a href="{{ route('admin.nav.index') }}" class="text-[#ffb400] hover:underline">Navigation</a> to create subsections.</p>
            </div>
            <div x-show="!selectedNavItem" class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                <p class="text-sm text-gray-500">Select a section first to view its subsections.</p>
            </div>
        </x-card>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-[#ffb400] text-gray-900 font-semibold rounded-lg hover:bg-[#e6a200] transition-colors">
                Update Section
            </button>
            <a href="{{ route('admin.home-page-sections.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
function sectionForm() {
    return {
        selectedNavItem: '{{ old('nav_item_id', $homePageSection->nav_item_id) }}',
        navLinks: [],
        selectedNavLinkIds: [],
        selectedSubsection: '',
        subsectionConfigs: {},
        
        async init() {
            // Initialize selectedNavLinkIds from saved data, ensuring proper type conversion
            const savedIds = @json(old('selected_nav_link_ids', $homePageSection->selected_nav_link_ids ?? null));
            if (savedIds && Array.isArray(savedIds) && savedIds.length > 0) {
                // Convert all to numbers for consistent comparison
                this.selectedNavLinkIds = savedIds.map(id => Number(id));
            } else {
                // If no saved selection, start with empty array (will be populated with all IDs when navLinks load)
                this.selectedNavLinkIds = [];
            }
            
            // Initialize subsection configs structure
            const savedConfigs = @json(old('subsection_configurations', $homePageSection->subsection_configurations ?? null));
            if (savedConfigs && typeof savedConfigs === 'object' && savedConfigs !== null) {
                // Convert keys to strings if needed and ensure all objects have required properties
                const configs = {};
                for (const [key, value] of Object.entries(savedConfigs)) {
                    const configObj = value || {};
                    configs[String(key)] = {
                        animation_style: configObj.animation_style || '',
                        layout_style: configObj.layout_style || ''
                    };
                }
                this.subsectionConfigs = configs;
            } else {
                this.subsectionConfigs = {};
            }
            
            if (this.selectedNavItem) {
                await this.loadNavLinks();
            }
        },
        
        async loadNavLinks() {
            if (!this.selectedNavItem) {
                this.navLinks = [];
                return;
            }
            
            try {
                console.log('Loading NavLinks for NavItem:', this.selectedNavItem);
                const response = await fetch(`/admin/home-page-sections/nav-links/${this.selectedNavItem}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('NavLinks received from API:', data);
                
                // Only use NavLinks from the database response - NO hardcoded items
                this.navLinks = Array.isArray(data) ? data : [];
                
                console.log('NavLinks after processing:', this.navLinks);
                console.log('Number of NavLinks:', this.navLinks.length);
                
                // If no saved selection (null or empty), select ALL navLinks by default
                if (this.selectedNavLinkIds.length === 0 && this.navLinks.length > 0) {
                    // Select all navLinks by default
                    this.selectedNavLinkIds = this.navLinks.map(l => Number(l.id));
                } else {
                    // When switching NavItems, clear selections that are no longer valid
                    const validIds = this.navLinks.map(l => Number(l.id));
                    this.selectedNavLinkIds = this.selectedNavLinkIds
                        .map(id => Number(id))
                        .filter(id => validIds.includes(id));
                    
                    // Also clean up subsection configs for removed items
                    const configKeys = Object.keys(this.subsectionConfigs);
                    const newConfigs = {};
                    configKeys.forEach(key => {
                        if (validIds.includes(Number(key))) {
                            newConfigs[key] = this.subsectionConfigs[key];
                        }
                    });
                    this.subsectionConfigs = newConfigs;
                }
            } catch (error) {
                console.error('Error loading nav links:', error);
                console.error('Error details:', error.message);
                // On error, show empty array - DO NOT show hardcoded fallback items
                this.navLinks = [];
            }
        },
        
        toggleNavLink(linkId) {
            const linkIdNum = Number(linkId);
            const index = this.selectedNavLinkIds.findIndex(id => Number(id) === linkIdNum);
            if (index > -1) {
                this.selectedNavLinkIds.splice(index, 1);
                // Remove config if subsection is unselected
                delete this.subsectionConfigs[String(linkId)];
            } else {
                this.selectedNavLinkIds.push(linkIdNum);
            }
        },
        
        getSubsectionTitle(linkId) {
            if (!linkId) return '';
            const link = this.navLinks.find(l => l.id == linkId);
            return link ? (link.title_translated || 'Untitled') : `Link #${linkId}`;
        },
        
        clearSubsectionConfig(linkId) {
            if (linkId) {
                const key = String(linkId);
                const newConfigs = { ...this.subsectionConfigs };
                delete newConfigs[key];
                this.subsectionConfigs = newConfigs;
                if (this.selectedSubsection == linkId) {
                    this.selectedSubsection = '';
                }
            }
        },
        
        getSubsectionAnimationStyle() {
            if (!this.selectedSubsection || !this.subsectionConfigs[this.selectedSubsection]) {
                return '';
            }
            return this.subsectionConfigs[this.selectedSubsection].animation_style || '';
        },
        
        setSubsectionAnimationStyle(value) {
            if (this.selectedSubsection) {
                const key = String(this.selectedSubsection);
                if (!this.subsectionConfigs[key]) {
                    this.ensureSubsectionConfig(this.selectedSubsection);
                }
                const newConfigs = { ...this.subsectionConfigs };
                if (!newConfigs[key]) {
                    newConfigs[key] = { animation_style: '', layout_style: '' };
                }
                newConfigs[key].animation_style = value;
                this.subsectionConfigs = newConfigs;
            }
        },
        
        getSubsectionLayoutStyle() {
            if (!this.selectedSubsection || !this.subsectionConfigs[this.selectedSubsection]) {
                return '';
            }
            return this.subsectionConfigs[this.selectedSubsection].layout_style || '';
        },
        
        setSubsectionLayoutStyle(value) {
            if (this.selectedSubsection) {
                const key = String(this.selectedSubsection);
                if (!this.subsectionConfigs[key]) {
                    this.ensureSubsectionConfig(this.selectedSubsection);
                }
                const newConfigs = { ...this.subsectionConfigs };
                if (!newConfigs[key]) {
                    newConfigs[key] = { animation_style: '', layout_style: '' };
                }
                newConfigs[key].layout_style = value;
                this.subsectionConfigs = newConfigs;
            }
        },
        
        ensureSubsectionConfig(linkId) {
            if (linkId) {
                const key = String(linkId);
                // Initialize synchronously - Alpine.js will handle reactivity
                if (!this.subsectionConfigs[key]) {
                    // Create new object to trigger reactivity
                    const newConfigs = { ...this.subsectionConfigs };
                    newConfigs[key] = {
                        animation_style: '',
                        layout_style: ''
                    };
                    this.subsectionConfigs = newConfigs;
                } else {
                    // Ensure properties exist even if object exists
                    const existing = this.subsectionConfigs[key];
                    if (!existing || !existing.hasOwnProperty('animation_style')) {
                        const newConfigs = { ...this.subsectionConfigs };
                        newConfigs[key] = {
                            ...(existing || {}),
                            animation_style: (existing && existing.animation_style) || '',
                            layout_style: (existing && existing.layout_style) || ''
                        };
                        this.subsectionConfigs = newConfigs;
                    }
                }
            }
        }
    }
}
</script>
@endsection

