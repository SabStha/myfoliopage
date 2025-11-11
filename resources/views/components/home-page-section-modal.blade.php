@props(['sectionId' => null])

<x-modal name="home-page-section-modal" maxWidth="4xl">
    <div class="p-6" x-data="homePageSectionForm({{ $sectionId ?: 'null' }})">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900" x-text="sectionId ? '{{ __('app.admin.home_page_section.edit_title') }}' : '{{ __('app.admin.home_page_section.create_title') }}'"></h2>
                <p class="text-gray-600 mt-1" x-text="sectionId ? '{{ __('app.admin.home_page_section.edit_description') }}' : '{{ __('app.admin.home_page_section.create_description') }}'"></p>
            </div>
            <button @click="$dispatch('close-modal', 'home-page-section-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitForm()" class="space-y-6" x-show="!showSuccess">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.home_page_section.section_configuration') }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.home_page_section.select_section') }}</label>
                        <select x-model="formData.nav_item_id" @change="loadNavLinks()" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#ffb400] focus:border-[#ffb400]">
                            <option value="">{{ __('app.admin.home_page_section.choose_section') }}</option>
                            <template x-for="navItem in navItems" :key="navItem.id">
                                <option :value="navItem.id" x-text="navItem.label"></option>
                            </template>
                        </select>
                        <p x-show="errors.nav_item_id" class="mt-1 text-sm text-red-600" x-text="errors.nav_item_id"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.nav.position') }} *</label>
                            <input type="number" x-model="formData.position" required min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#ffb400] focus:border-[#ffb400]">
                            <p x-show="errors.position" class="mt-1 text-sm text-red-600" x-text="errors.position"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.home_page_section.text_alignment') }}</label>
                            <select x-model="formData.text_alignment" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-[#ffb400] focus:border-[#ffb400]">
                                <option value="left">{{ __('app.admin.home_page_section.left') }}</option>
                                <option value="right">{{ __('app.admin.home_page_section.right') }}</option>
                            </select>
                            <p x-show="errors.text_alignment" class="mt-1 text-sm text-red-600" x-text="errors.text_alignment"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-dual-language-input 
                                name="title" 
                                label="{{ __('app.admin.home_page_section.title_optional') }}" 
                                :value="['en' => '', 'ja' => '']"
                                :placeholder="__('app.admin.home_page_section.title_placeholder')"
                            />
                            <p x-show="errors.title" class="mt-1 text-sm text-red-600" x-text="errors.title"></p>
                        </div>

                        <div>
                            <x-dual-language-input 
                                name="subtitle" 
                                label="{{ __('app.admin.home_page_section.subtitle_optional') }}" 
                                :value="['en' => '', 'ja' => '']"
                                :placeholder="__('app.admin.home_page_section.subtitle_placeholder')"
                            />
                            <p x-show="errors.subtitle" class="mt-1 text-sm text-red-600" x-text="errors.subtitle"></p>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" x-model="formData.enabled" class="rounded border-gray-300 text-[#ffb400] focus:ring-[#ffb400]">
                            <span class="text-sm font-medium text-gray-700">{{ __('app.admin.home_page_section.enabled') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.home_page_section.select_subsections') }}</h3>
                <p class="text-sm text-gray-600 mb-4" x-text="sectionId ? '{{ __('app.admin.home_page_section.subsections_default') }}' : '{{ __('app.admin.home_page_section.subsections_default') }}'"></p>
                
                <div x-show="navLinks.length > 0" class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 mb-2">{{ __('app.admin.home_page_section.subsections_checked') }}</p>
                    <template x-for="link in navLinks" :key="link.id">
                        <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input type="checkbox" :checked="selectedNavLinkIds.includes(link.id)" @change="toggleNavLink(link.id)" class="rounded border-gray-300 text-[#ffb400] focus:ring-[#ffb400]">
                            <span class="text-sm text-gray-700" x-text="link.title_translated || 'Untitled'"></span>
                        </label>
                    </template>
                </div>
                <div x-show="navLinks.length === 0 && formData.nav_item_id" class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <p class="text-sm text-gray-600">{{ __('app.admin.home_page_section.no_subsections') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.home_page_section.add_navlinks') }} <a href="{{ route('admin.nav.index') }}" target="_blank" class="text-[#ffb400] hover:underline">{{ __('app.admin.home_page_section.navigation') }}</a> {{ __('app.admin.home_page_section.to_create_subsections') }}</p>
                </div>
                <div x-show="!formData.nav_item_id" class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <p class="text-sm text-gray-500">{{ __('app.admin.home_page_section.select_section_first') }}</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <button type="button" @click="$dispatch('close-modal', 'home-page-section-modal')" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('app.common.cancel') }}
                </button>
                <button type="submit" :disabled="loading" class="px-6 py-2 bg-[#ffb400] text-gray-900 font-semibold rounded-lg hover:bg-[#e6a200] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading" x-text="sectionId ? '{{ __('app.admin.home_page_section.update_section') }}' : '{{ __('app.admin.home_page_section.create_section') }}'"></span>
                    <span x-show="loading">{{ __('app.admin.home_page_section.saving') }}</span>
                </button>
            </div>
        </form>

        <!-- Success Message -->
        <div x-show="showSuccess" class="text-center py-8">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('app.admin.home_page_section.section_created') }}</h3>
            <p class="text-gray-600 mb-8">{{ __('app.admin.home_page_section.what_next') }}</p>
            <div class="flex items-center justify-center gap-4">
                <button @click="viewSectionList()" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('app.admin.home_page_section.see_section_list') }}
                </button>
                <button @click="continueToSubnav()" class="px-6 py-2 bg-[#ffb400] text-gray-900 font-semibold rounded-lg hover:bg-[#e6a200] transition-colors">
                    {{ __('app.admin.home_page_section.continue_to_subnav') }}
                </button>
            </div>
        </div>
    </div>

    <script>
    function homePageSectionForm(sectionId) {
        return {
            sectionId: sectionId,
            loading: false,
            showSuccess: false,
            navItems: [],
            navLinks: [],
            selectedNavLinkIds: [],
            errors: {},
            createdNavItemId: null,
            formData: {
                nav_item_id: '',
                position: 0,
                text_alignment: 'left',
                title: '',
                subtitle: '',
                enabled: true,
            },
            
            async init() {
                await this.loadNavItems();
                
                // Listen for load event
                window.addEventListener('load-home-page-section', (e) => {
                    this.sectionId = e.detail.id;
                    this.loadSection();
                });
                
                if (this.sectionId) {
                    await this.loadSection();
                } else {
                    // Get default position for new sections
                    try {
                        const response = await fetch('/admin/home-page-sections');
                        const html = await response.text();
                        // Count sections from the table or use a simpler approach
                        const sectionCount = (html.match(/<tr class="hover:bg-gray-50/g) || []).length;
                        this.formData.position = sectionCount;
                    } catch (error) {
                        this.formData.position = 0;
                    }
                }
            },
            
            async loadNavItems() {
                try {
                    const response = await fetch('/admin/home-page-sections/nav-items/list');
                    if (!response.ok) throw new Error('Failed to load nav items');
                    this.navItems = await response.json();
                } catch (error) {
                    console.error('Error loading nav items:', error);
                    this.navItems = [];
                }
            },
            
            async loadSection() {
                try {
                    const response = await fetch(`/api/home-page-sections/${this.sectionId}`);
                    if (!response.ok) throw new Error('Failed to load section');
                    const data = await response.json();
                    
                    this.formData = {
                        nav_item_id: data.nav_item_id,
                        position: data.position,
                        text_alignment: data.text_alignment,
                        enabled: data.enabled,
                    };
                    
                    // Set title and subtitle values in hidden inputs
                    const titleEnInput = document.querySelector('input[name="title[en]"]');
                    const titleJaInput = document.querySelector('input[name="title[ja]"]');
                    const subtitleEnInput = document.querySelector('input[name="subtitle[en]"]');
                    const subtitleJaInput = document.querySelector('input[name="subtitle[ja]"]');
                    
                    // Handle title (can be array or string for backward compatibility)
                    if (titleEnInput && data.title) {
                        if (typeof data.title === 'object' && data.title !== null) {
                            titleEnInput.value = data.title.en || '';
                        } else {
                            titleEnInput.value = data.title || '';
                        }
                        titleEnInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (titleJaInput && data.title) {
                        if (typeof data.title === 'object' && data.title !== null) {
                            titleJaInput.value = data.title.ja || '';
                        } else {
                            titleJaInput.value = '';
                        }
                        titleJaInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    
                    // Handle subtitle (can be array or string for backward compatibility)
                    if (subtitleEnInput && data.subtitle) {
                        if (typeof data.subtitle === 'object' && data.subtitle !== null) {
                            subtitleEnInput.value = data.subtitle.en || '';
                        } else {
                            subtitleEnInput.value = data.subtitle || '';
                        }
                        subtitleEnInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (subtitleJaInput && data.subtitle) {
                        if (typeof data.subtitle === 'object' && data.subtitle !== null) {
                            subtitleJaInput.value = data.subtitle.ja || '';
                        } else {
                            subtitleJaInput.value = '';
                        }
                        subtitleJaInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    
                    const savedIds = data.selected_nav_link_ids;
                    if (savedIds && Array.isArray(savedIds) && savedIds.length > 0) {
                        this.selectedNavLinkIds = savedIds.map(id => Number(id));
                    }
                    
                    if (this.formData.nav_item_id) {
                        await this.loadNavLinks();
                        // If no saved selection, select all
                        if (this.selectedNavLinkIds.length === 0 && this.navLinks.length > 0) {
                            this.selectedNavLinkIds = this.navLinks.map(l => Number(l.id));
                        }
                    }
                } catch (error) {
                    console.error('Error loading section:', error);
                    alert('Failed to load section data');
                }
            },
            
            async loadNavLinks() {
                if (!this.formData.nav_item_id) {
                    this.navLinks = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/admin/home-page-sections/nav-links/${this.formData.nav_item_id}`);
                    if (!response.ok) throw new Error('Failed to load nav links');
                    
                    const data = await response.json();
                    this.navLinks = Array.isArray(data) ? data : [];
                    
                    // If creating new section or no saved selection, select all by default
                    if (!this.sectionId || this.selectedNavLinkIds.length === 0) {
                        this.selectedNavLinkIds = this.navLinks.map(l => Number(l.id));
                    }
                } catch (error) {
                    console.error('Error loading nav links:', error);
                    this.navLinks = [];
                }
            },
            
            toggleNavLink(linkId) {
                const linkIdNum = Number(linkId);
                const index = this.selectedNavLinkIds.findIndex(id => Number(id) === linkIdNum);
                if (index > -1) {
                    this.selectedNavLinkIds.splice(index, 1);
                } else {
                    this.selectedNavLinkIds.push(linkIdNum);
                }
            },
            
            async submitForm() {
                this.loading = true;
                this.errors = {};
                
                try {
                    const formDataToSend = new FormData();
                    formDataToSend.append('nav_item_id', this.formData.nav_item_id);
                    formDataToSend.append('position', this.formData.position);
                    formDataToSend.append('text_alignment', this.formData.text_alignment);
                    
                    // Get title and subtitle values from hidden inputs
                    const titleEnInput = document.querySelector('input[name="title[en]"]');
                    const titleJaInput = document.querySelector('input[name="title[ja]"]');
                    const subtitleEnInput = document.querySelector('input[name="subtitle[en]"]');
                    const subtitleJaInput = document.querySelector('input[name="subtitle[ja]"]');
                    
                    if (titleEnInput || titleJaInput) {
                        formDataToSend.append('title[en]', titleEnInput?.value || '');
                        formDataToSend.append('title[ja]', titleJaInput?.value || '');
                    }
                    
                    if (subtitleEnInput || subtitleJaInput) {
                        formDataToSend.append('subtitle[en]', subtitleEnInput?.value || '');
                        formDataToSend.append('subtitle[ja]', subtitleJaInput?.value || '');
                    }
                    
                    formDataToSend.append('enabled', this.formData.enabled ? '1' : '0');
                    
                    // Add selected nav link IDs
                    this.selectedNavLinkIds.forEach(id => {
                        formDataToSend.append('selected_nav_link_ids[]', id);
                    });
                    
                    const url = this.sectionId 
                        ? `/admin/home-page-sections/${this.sectionId}`
                        : '/admin/home-page-sections';
                    
                    const response = await fetch(url, {
                        method: this.sectionId ? 'PUT' : 'POST',
                        body: formDataToSend,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok && result.success) {
                        if (!this.sectionId) {
                            // For new sections, show success popup
                            this.createdNavItemId = this.formData.nav_item_id;
                            this.showSuccess = true;
                        } else {
                            // For edits, just reload
                            this.$dispatch('close-modal', 'home-page-section-modal');
                            window.location.reload();
                        }
                    } else {
                        if (result.errors) {
                            this.errors = result.errors;
                        } else {
                            alert(result.message || 'Failed to save section');
                        }
                    }
                } catch (error) {
                    console.error('Error submitting form:', error);
                    alert('Failed to save section');
                } finally {
                    this.loading = false;
                }
            },
            
            viewSectionList() {
                this.$dispatch('close-modal', 'home-page-section-modal');
                window.location.reload();
            },
            
            continueToSubnav() {
                if (this.createdNavItemId) {
                    window.location.href = `/admin/nav/${this.createdNavItemId}/links`;
                } else {
                    // Fallback to reloading the page
                    this.viewSectionList();
                }
            }
        }
    }
    </script>
</x-modal>
