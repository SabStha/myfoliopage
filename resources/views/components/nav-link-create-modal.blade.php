@props(['navId' => null])

<x-modal name="nav-link-create-modal" maxWidth="5xl">
    <div class="p-6" x-data="navLinkCreateForm({{ $navId ?: 'null' }})">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('app.admin.nav_link.create_new_item') }}</h2>
                <p class="text-gray-600 mt-1">{{ __('app.admin.nav_link.add_new_item_description') }}</p>
            </div>
            <button @click="$dispatch('close-modal', 'nav-link-create-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitForm()" class="space-y-6" x-show="!showSuccess">
            {{-- Basic Information Section --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('app.admin.nav_link.basic_information') }}</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <x-dual-language-input 
                            name="title" 
                            label="{{ __('app.admin.nav_link.title') }}" 
                            :value="['en' => '', 'ja' => '']"
                            :placeholder="__('app.admin.nav_link.title_placeholder')"
                            :required="true"
                        />
                        <p x-show="errors.title" class="mt-1 text-sm text-red-600" x-text="errors.title"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.nav_link.position') }}</label>
                        <input type="number" x-model="formData.position" min="0" placeholder="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <p x-show="errors.position" class="mt-1 text-sm text-red-600" x-text="errors.position"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.nav_link.progress_label') }}</label>
                        <input type="number" x-model="formData.progress" min="0" max="100" placeholder="{{ __('app.admin.nav_link.progress_placeholder') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <p x-show="errors.progress" class="mt-1 text-sm text-red-600" x-text="errors.progress"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.nav_link.issued_at') }}</label>
                        <input type="date" x-model="formData.issued_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <p x-show="errors.issued_at" class="mt-1 text-sm text-red-600" x-text="errors.issued_at"></p>
                    </div>
                </div>
            </div>

            {{-- Categories Section --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('app.admin.nav_link.categories') }}</h3>
                
                <div x-show="categories.length === 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-900">{{ __('app.admin.nav_link.no_categories_available') }}. {{ __('app.admin.nav_link.categories_will_appear') }}</p>
                </div>
                
                <div x-show="categories.length > 0" class="bg-gray-50 rounded-lg border border-gray-200 p-4 max-h-64 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="cat in categories" :key="cat.id">
                            <label class="flex items-center gap-3 p-3 bg-white rounded-lg border-2 border-gray-200 hover:border-teal-300 hover:bg-teal-50/50 cursor-pointer transition-all">
                                <input type="checkbox" :value="cat.id" x-model="formData.categories" class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500">
                                <span class="flex-1 text-sm font-medium text-gray-700" x-text="cat.name || cat.slug"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Media & Files Section --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('app.admin.nav_link.media_files') }}</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.nav_link.image_optional') }}</label>
                        <input type="file" name="image" accept="image/*" @change="previewImage($event)" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <div x-show="imagePreview" class="mt-3">
                            <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded-lg border-2 border-teal-200">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.nav_link.pdf_document_optional') }}</label>
                        <input type="file" name="document" accept="application/pdf" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                </div>
            </div>

            {{-- URLs Section --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('app.admin.nav_link.links_urls') }}</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.nav_link.url_optional') }}</label>
                        <input type="url" x-model="formData.url" placeholder="https://example.com" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <p x-show="errors.url" class="mt-1 text-sm text-red-600" x-text="errors.url"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.nav_link.proof_url_optional') }}</label>
                        <input type="url" x-model="formData.proof_url" placeholder="https://example.com/proof" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <p x-show="errors.proof_url" class="mt-1 text-sm text-red-600" x-text="errors.proof_url"></p>
                    </div>
                </div>
            </div>

            {{-- Notes Section --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">{{ __('app.admin.nav_link.additional_notes') }}</h3>
                <textarea x-model="formData.notes" rows="5" placeholder="{{ __('app.admin.nav_link.notes_placeholder') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-4">
                <button type="button" @click="$dispatch('close-modal', 'nav-link-create-modal')" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('app.common.cancel') }}
                </button>
                <button type="submit" :disabled="loading" class="px-6 py-2 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">{{ __('app.admin.nav_link.create_item') }}</span>
                    <span x-show="loading">{{ __('app.admin.nav_link.creating') }}</span>
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
            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('app.admin.nav_link.item_created_successfully') }}</h3>
            <p class="text-gray-600 mb-8">{{ __('app.admin.nav_link.what_next') }}</p>
            <div class="flex items-center justify-center gap-4">
                <button @click="viewList()" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('app.admin.nav_link.see_list') }}
                </button>
                <button @click="continueToCategories()" class="px-6 py-2 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition-colors">
                    {{ __('app.admin.nav_link.continue_to_categories') }}
                </button>
            </div>
        </div>
    </div>

    <script>
    function navLinkCreateForm(navId) {
        return {
            navId: navId,
            loading: false,
            showSuccess: false,
            categories: [],
            errors: {},
            imagePreview: null,
            createdNavLinkId: null,
            formData: {
                url: '',
                proof_url: '',
                progress: '',
                issued_at: '',
                notes: '',
                position: 0,
                categories: [],
            },
            
            async init() {
                if (this.navId) {
                    await this.loadCategories();
                }
            },
            
            async loadCategories() {
                try {
                    // Fetch categories from the create page
                    const response = await fetch(`/admin/nav/${this.navId}/links/create`);
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Extract categories from the page
                    const categoryInputs = doc.querySelectorAll('input[name="categories[]"]');
                    this.categories = Array.from(categoryInputs).map(input => {
                        const label = input.closest('label');
                        const nameSpan = label?.querySelector('span');
                        return {
                            id: parseInt(input.value),
                            name: nameSpan?.textContent?.trim() || '',
                            slug: nameSpan?.textContent?.trim() || ''
                        };
                    });
                } catch (error) {
                    console.error('Error loading categories:', error);
                    this.categories = [];
                }
            },
            
            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },
            
            async submitForm() {
                this.loading = true;
                this.errors = {};
                
                try {
                    const formDataToSend = new FormData();
                    
                    // Get title values from hidden inputs
                    const titleEnInput = document.querySelector('input[name="title[en]"]');
                    const titleJaInput = document.querySelector('input[name="title[ja]"]');
                    formDataToSend.append('title[en]', titleEnInput?.value || '');
                    formDataToSend.append('title[ja]', titleJaInput?.value || '');
                    
                    formDataToSend.append('position', this.formData.position || 0);
                    formDataToSend.append('progress', this.formData.progress || '');
                    formDataToSend.append('issued_at', this.formData.issued_at || '');
                    formDataToSend.append('notes', this.formData.notes || '');
                    formDataToSend.append('url', this.formData.url || '');
                    formDataToSend.append('proof_url', this.formData.proof_url || '');
                    
                    // Add categories
                    this.formData.categories.forEach(id => {
                        formDataToSend.append('categories[]', id);
                    });
                    
                    // Add files
                    const imageInput = document.querySelector('input[name="image"]');
                    if (imageInput?.files[0]) {
                        formDataToSend.append('image', imageInput.files[0]);
                    }
                    
                    const documentInput = document.querySelector('input[name="document"]');
                    if (documentInput?.files[0]) {
                        formDataToSend.append('document', documentInput.files[0]);
                    }
                    
                    const response = await fetch(`/admin/nav/${this.navId}/links`, {
                        method: 'POST',
                        body: formDataToSend,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok && result.success) {
                        this.createdNavLinkId = result.nav_link_id;
                        this.showSuccess = true;
                    } else {
                        if (result.errors) {
                            this.errors = result.errors;
                        } else {
                            alert(result.message || 'Failed to create item');
                        }
                    }
                } catch (error) {
                    console.error('Error submitting form:', error);
                    alert('Failed to create item');
                } finally {
                    this.loading = false;
                }
            },
            
            viewList() {
                this.$dispatch('close-modal', 'nav-link-create-modal');
                window.location.reload();
            },
            
            continueToCategories() {
                if (this.createdNavLinkId && this.navId) {
                    window.location.href = `/admin/nav/${this.navId}/links/${this.createdNavLinkId}/categories`;
                } else {
                    this.viewList();
                }
            }
        }
    }
    </script>
</x-modal>


