@props(['navId' => null, 'linkId' => null])

<x-modal name="category-create-modal" maxWidth="2xl">
    <div class="p-6" x-data="categoryCreateForm({{ $navId ?: 'null' }}, {{ $linkId ?: 'null' }})">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('app.admin.categories.create_new_category_title') }}</h2>
                <p class="text-gray-600 mt-1">{{ __('app.admin.categories.add_new_category_description') }}</p>
            </div>
            <button @click="$dispatch('close-modal', 'category-create-modal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form @submit.prevent="submitForm()" class="space-y-6" x-show="!showSuccess">
            <div>
                <x-dual-language-input 
                    name="name" 
                    label="{{ __('app.admin.categories.name_required') }}" 
                    :value="['en' => '', 'ja' => '']"
                    placeholder="e.g., Java Pages"
                    :required="true"
                />
                <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.categories.display_name_hint') }}</p>
                <p x-show="errors.name" class="mt-1 text-sm text-red-600" x-text="errors.name"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.categories.slug_optional') }}</label>
                <input x-model="formData.slug" placeholder="{{ __('app.admin.categories.slug_auto_generated') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.categories.url_friendly_identifier') }}</p>
                <p x-show="errors.slug" class="mt-1 text-sm text-red-600" x-text="errors.slug"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.categories.color_optional') }}</label>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="formData.color" placeholder="#3b82f6" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                    <input type="color" :value="formData.color || '#3b82f6'" @input="formData.color = $event.target.value" class="w-12 h-10 cursor-pointer bg-white border border-gray-300 rounded-md">
                </div>
                <p x-show="errors.color" class="mt-1 text-sm text-red-600" x-text="errors.color"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.categories.position_optional') }}</label>
                <input type="number" x-model="formData.position" value="0" min="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                <p x-show="errors.position" class="mt-1 text-sm text-red-600" x-text="errors.position"></p>
            </div>

            <div class="flex items-center justify-end gap-4">
                <button type="button" @click="$dispatch('close-modal', 'category-create-modal')" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('app.admin.nav_link.cancel') }}
                </button>
                <button type="submit" :disabled="loading" class="px-6 py-2 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">{{ __('app.admin.categories.create') }}</span>
                    <span x-show="loading">{{ __('app.admin.categories.creating') }}</span>
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
            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('app.admin.categories.category_created_successfully') }}</h3>
            <p class="text-gray-600 mb-8">{{ __('app.admin.categories.what_would_you_like_to_do_next') }}</p>
            <div class="flex items-center justify-center gap-4">
                <button @click="viewList()" class="px-6 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('app.admin.categories.see_the_list') }}
                </button>
                <button @click="continueToItems()" class="px-6 py-2 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition-colors">
                    {{ __('app.admin.categories.continue_to_items') }}
                </button>
            </div>
        </div>
    </div>

    <script>
    function categoryCreateForm(navId, linkId) {
        return {
            navId: navId,
            linkId: linkId,
            loading: false,
            showSuccess: false,
            errors: {},
            createdCategoryId: null,
            formData: {
                name: { en: '', ja: '' },
                slug: '',
                color: '',
                position: 0,
            },
            
            async submitForm() {
                this.loading = true;
                this.errors = {};
                
                try {
                    const formDataToSend = new FormData();
                    
                    // Get name values from hidden inputs
                    const nameEnInput = document.querySelector('input[name="name[en]"]');
                    const nameJaInput = document.querySelector('input[name="name[ja]"]');
                    
                    if (nameEnInput || nameJaInput) {
                        formDataToSend.append('name[en]', nameEnInput?.value || '');
                        formDataToSend.append('name[ja]', nameJaInput?.value || '');
                    }
                    
                    formDataToSend.append('slug', this.formData.slug || '');
                    formDataToSend.append('color', this.formData.color || '');
                    formDataToSend.append('position', this.formData.position || 0);
                    
                    const response = await fetch(`/admin/nav/${this.navId}/links/${this.linkId}/categories`, {
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
                        this.createdCategoryId = result.category_id;
                        this.showSuccess = true;
                    } else {
                        if (result.errors) {
                            this.errors = result.errors;
                        } else {
                            alert(result.message || '{{ __('app.admin.categories.failed_to_create_category') }}');
                        }
                    }
                } catch (error) {
                    console.error('Error submitting form:', error);
                    alert('{{ __('app.admin.categories.failed_to_create_category') }}');
                } finally {
                    this.loading = false;
                }
            },
            
            viewList() {
                this.$dispatch('close-modal', 'category-create-modal');
                window.location.reload();
            },
            
            continueToItems() {
                if (this.createdCategoryId && this.navId && this.linkId) {
                    window.location.href = `/admin/nav/${this.navId}/links/${this.linkId}/categories/${this.createdCategoryId}/items`;
                } else {
                    this.viewList();
                }
            }
        }
    }
    </script>
</x-modal>


