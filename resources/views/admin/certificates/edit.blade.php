@extends('layouts.app')
@section('title', 'Edit Certificate')
@section('content')
    <script>
        window.certificateEditData = {
            sections: @js($sections->map(function($s) {
                return ['id' => $s->id, 'name' => $s->title, 'title' => $s->title, 'category_id' => $s->category_id, 'category_name' => $s->category->name];
            })),
            selectedCategories: @js($certificate->categories->pluck('id')->toArray())
        };
        
        // Register Alpine.js component data
        window.registerCertificateEditComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                try {
                    window.Alpine.data('certificateEdit', () => ({
                        sections: window.certificateEditData?.sections || [],
                        selectedCategories: window.certificateEditData?.selectedCategories || [],
                        showNewSectionForm: false,
                        newSectionName: '',
                        newSectionCategoryId: '',
                        newSectionDescription: '',
                        loading: false,
                        imagePreview: @js($certificate->media->where('type', 'image')->first() ? asset('storage/' . $certificate->media->where('type', 'image')->first()->path) : null),
                        previewData: {
                            title: @js(old('title', $certificate->title ?? '')),
                            provider: @js(old('provider', $certificate->provider ?? '')),
                            issued_by: @js(old('issued_by', $certificate->issued_by ?? '')),
                            credential_id: @js(old('credential_id', $certificate->credential_id ?? '')),
                            verify_url: @js(old('verify_url', $certificate->verify_url ?? '')),
                            issued_at: @js(old('issued_at', optional($certificate->issued_at)->format('Y-m-d') ?? '')),
                            expiry_date: @js(old('expiry_date', optional($certificate->expiry_date)->format('Y-m-d') ?? '')),
                            has_expiry: @js(old('has_expiry', $certificate->has_expiry ?? false)),
                            level: @js(old('level', $certificate->level ?? '')),
                            learning_hours: @js(old('learning_hours', $certificate->learning_hours ?? '')),
                            learning_outcomes: @js(old('learning_outcomes', $certificate->learning_outcomes ?? '')),
                            reflection: @js(old('reflection', $certificate->reflection ?? '')),
                            status: @js(old('status', $certificate->status ?? 'completed')),
                            tags: @js(old('tags', $certificate->tags->pluck('name')->implode(', ') ?? ''))
                        },
                        normalizeTags(tags) {
                            if (!tags) return '';
                            return tags.split(',')
                                .map(t => t.trim().toLowerCase())
                                .filter(t => t)
                                .slice(0, 5)
                                .join(', ');
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
                        init() {
                            // Sync previewData with form field values on load
                            // Use $nextTick to ensure DOM is ready
                            this.$nextTick(() => {
                                const form = this.$el.closest('form') || document.querySelector('form');
                                if (form) {
                                    const titleInput = form.querySelector('[name="title"]');
                                    if (titleInput) this.previewData.title = titleInput.value || '';
                                    const providerInput = form.querySelector('[name="provider"]');
                                    if (providerInput) this.previewData.provider = providerInput.value || '';
                                    const issuedByInput = form.querySelector('[name="issued_by"]');
                                    if (issuedByInput) this.previewData.issued_by = issuedByInput.value || '';
                                    const credentialIdInput = form.querySelector('[name="credential_id"]');
                                    if (credentialIdInput) this.previewData.credential_id = credentialIdInput.value || '';
                                    const verifyUrlInput = form.querySelector('[name="verify_url"]');
                                    if (verifyUrlInput) this.previewData.verify_url = verifyUrlInput.value || '';
                                    const issuedAtInput = form.querySelector('[name="issued_at"]');
                                    if (issuedAtInput) this.previewData.issued_at = issuedAtInput.value || '';
                                    const expiryDateInput = form.querySelector('[name="expiry_date"]');
                                    if (expiryDateInput) this.previewData.expiry_date = expiryDateInput.value || '';
                                    const hasExpiryCheckbox = form.querySelector('[name="has_expiry"]');
                                    if (hasExpiryCheckbox) this.previewData.has_expiry = hasExpiryCheckbox.checked;
                                    const levelSelect = form.querySelector('[name="level"]');
                                    if (levelSelect) this.previewData.level = levelSelect.value || '';
                                    const learningHoursInput = form.querySelector('[name="learning_hours"]');
                                    if (learningHoursInput) this.previewData.learning_hours = learningHoursInput.value || '';
                                    const learningOutcomesTextarea = form.querySelector('[name="learning_outcomes"]');
                                    if (learningOutcomesTextarea) this.previewData.learning_outcomes = learningOutcomesTextarea.value || '';
                                    const reflectionTextarea = form.querySelector('[name="reflection"]');
                                    if (reflectionTextarea) this.previewData.reflection = reflectionTextarea.value || '';
                                    const statusSelect = form.querySelector('[name="status"]');
                                    if (statusSelect) this.previewData.status = statusSelect.value || 'completed';
                                    const tagsInput = form.querySelector('[name="tags"]');
                                    if (tagsInput) this.previewData.tags = tagsInput.value || '';
                                }
                            });
                        },
                        handleCategoryChange(event) {
                            const select = event.target;
                            this.selectedCategories = Array.from(select.selectedOptions).map(opt => opt.value);
                            if (this.selectedCategories.length > 0 && !this.newSectionCategoryId) {
                                this.newSectionCategoryId = this.selectedCategories[0];
                            }
                        },
                        async createNewSection() {
                            if (!this.newSectionName || !this.newSectionCategoryId) {
                                alert('Please enter section name and select a category');
                                return;
                            }
                            this.loading = true;
                            try {
                                const response = await fetch('{{ route('admin.sections.quick-create') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        category_id: this.newSectionCategoryId,
                                        name: this.newSectionName,
                                        description: this.newSectionDescription,
                                        position: 0
                                    })
                                });
                                const data = await response.json();
                                if (data.success) {
                                    const sectionTitle = data.section.title || data.section.name || '';
                                    this.sections.push({
                                        id: data.section.id,
                                        name: sectionTitle,
                                        title: sectionTitle,
                                        category_id: data.section.category.id,
                                        category_name: data.section.category.name
                                    });
                                    const select = document.querySelector('select[name=\'sections[]\']');
                                    if (select) {
                                        const option = document.createElement('option');
                                        option.value = data.section.id;
                                        option.textContent = data.section.category.name + ' → ' + sectionTitle;
                                        option.selected = true;
                                        select.appendChild(option);
                                    }
                                    this.newSectionName = '';
                                    this.newSectionDescription = '';
                                    this.newSectionCategoryId = this.selectedCategories && this.selectedCategories.length > 0 ? this.selectedCategories[0] : '';
                                    this.showNewSectionForm = false;
                                } else {
                                    alert('Error creating section');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('Error creating section');
                            } finally {
                                this.loading = false;
                            }
                        }
                    }));
                } catch (e) {
                    console.error('Error registering Alpine component:', e);
                }
            }
        };
        
        // Register immediately if Alpine is already loaded
        if (window.Alpine && window.Alpine.data) {
            window.registerCertificateEditComponent();
        } else {
            document.addEventListener('alpine:init', window.registerCertificateEditComponent);
        }
    </script>

    {{-- Hero Header --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Edit Certificate</h1>
                    <p class="text-blue-100 text-lg">Update certificate details and verification information</p>
                </div>
                <div class="hidden md:block">
                    <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-data="certificateEdit()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <form method="POST" action="{{ route('admin.certificates.update', $certificate) }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            @method('PUT')
            
            {{-- Left Column: Form (2/3 width) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Basic Information Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Basic Information
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <x-dual-language-input 
                            name="title" 
                            label="Title" 
                            :value="$certificate->getTranslations('title')"
                            placeholder="e.g., AWS Certified Solutions Architect"
                            required="true"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-dual-language-input 
                                    name="provider" 
                                    label="Provider" 
                                    :value="$certificate->getTranslations('provider')"
                                    placeholder="e.g., AWS, Coursera, TryHackMe"
                                    required="true"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Issued By
                                </label>
                                <input 
                                    name="issued_by" 
                                    value="{{ old('issued_by', $certificate->issued_by) }}"
                                    @input="previewData.issued_by = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="e.g., AWS Training and Certification" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Issued At <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    name="issued_at" 
                                    value="{{ old('issued_at', optional($certificate->issued_at)->format('Y-m-d')) }}"
                                    @input="previewData.issued_at = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    required />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Credential ID
                                </label>
                                <input 
                                    name="credential_id" 
                                    value="{{ old('credential_id', $certificate->credential_id) }}"
                                    @input="previewData.credential_id = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none font-mono text-sm" 
                                    placeholder="e.g., AB-12345678" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Verify URL
                            </label>
                            <input 
                                type="url" 
                                name="verify_url" 
                                value="{{ old('verify_url', $certificate->verify_url) }}"
                                @input="previewData.verify_url = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                placeholder="https://coursera.org/verify/XYZ123" />
                            <p class="text-xs text-gray-500 mt-1">Required if no certificate image is uploaded</p>
                        </div>
                    </div>
                </div>

                {{-- Proof & Verification Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Proof & Verification
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        @if($certificate->media->where('type', 'image')->first())
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Current Certificate Image</label>
                                <div class="relative inline-block">
                                    <img src="{{ asset('storage/' . $certificate->media->where('type', 'image')->first()->path) }}" alt="Current certificate" class="max-w-xs rounded-lg border border-gray-300">
                                    <label class="flex items-center gap-2 mt-2 text-sm text-gray-600">
                                        <input type="checkbox" name="delete_image" value="{{ $certificate->media->where('type', 'image')->first()->id }}" class="rounded">
                                        <span>Delete current image</span>
                                    </label>
                                </div>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Certificate Image / File
                            </label>
                            <input 
                                type="file" 
                                name="image" 
                                accept="image/*,.pdf"
                                @change="previewImage($event)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none" />
                            <p class="text-xs text-gray-500 mt-1">Upload PNG, JPG, or PDF. Required if no Verify URL is provided.</p>
                            <div x-show="imagePreview" class="mt-4">
                                <img :src="imagePreview" alt="Preview" class="max-w-xs rounded-lg border border-gray-300">
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <input 
                                type="hidden" 
                                name="has_expiry" 
                                value="0" />
                            <input 
                                type="checkbox" 
                                name="has_expiry" 
                                id="has_expiry"
                                value="1"
                                {{ old('has_expiry', $certificate->has_expiry) ? 'checked' : '' }}
                                x-model="previewData.has_expiry"
                                class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label for="has_expiry" class="text-sm font-semibold text-gray-700">Certificate has expiration date</label>
                        </div>

                        <div x-show="previewData && previewData.has_expiry">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Expiry Date
                            </label>
                            <input 
                                type="date" 
                                name="expiry_date" 
                                value="{{ old('expiry_date', optional($certificate->expiry_date)->format('Y-m-d')) }}"
                                @input="previewData.expiry_date = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none" />
                        </div>
                    </div>
                </div>

                {{-- Context & Impact Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            Context & Impact
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Level
                                </label>
                                <select 
                                    name="level" 
                                    @change="previewData.level = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                    <option value="">Select level</option>
                                    <option value="Beginner" {{ old('level', $certificate->level) === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                    <option value="Intermediate" {{ old('level', $certificate->level) === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                    <option value="Advanced" {{ old('level', $certificate->level) === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Learning Hours
                                </label>
                                <input 
                                    type="number" 
                                    name="learning_hours" 
                                    min="0"
                                    value="{{ old('learning_hours', $certificate->learning_hours) }}"
                                    @input="previewData.learning_hours = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                    placeholder="e.g., 12" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Learning Outcomes / Key Topics (one per line)
                            </label>
                            <textarea 
                                name="learning_outcomes" 
                                @input="previewData.learning_outcomes = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none">{{ old('learning_outcomes', $certificate->learning_outcomes) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Enter one topic per line</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Reflection / Impact (2-4 sentences)
                            </label>
                            <textarea 
                                name="reflection" 
                                @input="previewData.reflection = $event.target.value"
                                rows="4" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none">{{ old('reflection', $certificate->reflection) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Status
                            </label>
                            <select 
                                name="status" 
                                @change="previewData.status = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                <option value="completed" {{ old('status', $certificate->status ?? 'completed') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="in_progress" {{ old('status', $certificate->status ?? 'completed') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Traceability & Portfolio Integration Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Traceability & Portfolio Integration
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Link to Project
                            </label>
                            <select 
                                name="project_id" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none">
                                <option value="">Select a project (optional)</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id', $certificate->project_id) == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Link this certificate to a relevant project</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Categories
                            </label>
                            <select 
                                name="categories[]" 
                                multiple 
                                @change="handleCategoryChange($event)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none min-h-[150px]" 
                                size="5">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $certificate->categories->contains($category->id) ? 'selected' : '' }}>{{ $category->getTranslated('name') }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple categories</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Sections (grouping within categories)
                            </label>
                            <select 
                                name="sections[]" 
                                multiple 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none min-h-[150px]" 
                                size="5">
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ $certificate->sections->contains($section->id) ? 'selected' : '' }}>{{ $section->category->getTranslated('name') }} → {{ $section->getTranslated('title') }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple sections.</p>
                            
                            <div class="mt-4">
                                <button type="button" @click="showNewSectionForm = !showNewSectionForm" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors border border-blue-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add New Section
                                </button>
                            </div>
                            
                            <div x-show="showNewSectionForm" x-cloak x-transition class="mt-4 p-6 bg-gradient-to-br from-teal-50 to-cyan-50 rounded-xl border-2 border-teal-200">
                                <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Create New Section
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                        <select name="new_section_category_id" x-model="newSectionCategoryId" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" required>
                                            <option value="">Select a category...</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Section Name</label>
                                        <input type="text" x-model="newSectionName" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" placeholder="e.g., Lesson 1">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description (optional)</label>
                                        <textarea x-model="newSectionDescription" rows="3" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none resize-none" placeholder="Brief description..."></textarea>
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="button" @click="createNewSection()" :disabled="loading" class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-lg font-medium disabled:opacity-50 transition-all shadow-md hover:shadow-lg">
                                            <span x-show="!loading">Create & Add</span>
                                            <span x-show="loading" class="flex items-center gap-2">
                                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Saving...
                                            </span>
                                        </button>
                                        <button type="button" @click="showNewSectionForm = false; newSectionName = ''; newSectionDescription = ''; newSectionCategoryId = selectedCategories && selectedCategories.length > 0 ? selectedCategories[0] : '';" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tags (max 5, comma separated)
                            </label>
                            <input 
                                name="tags" 
                                value="{{ old('tags', $certificate->tags->pluck('name')->implode(', ')) }}"
                                @input="previewData.tags = normalizeTags($event.target.value)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none" 
                                placeholder="cloud, security, aws" />
                            <p class="text-xs text-gray-500 mt-1">Tags are automatically normalized (lowercase, trimmed, max 5)</p>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('admin.certificates.index') }}" class="px-6 py-3 text-lg font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-md">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 text-lg font-semibold bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                        Update Certificate
                    </button>
                </div>
            </div>

            {{-- Right Column: Live Preview (1/3 width) --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Live Preview
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            {{-- Image Preview --}}
                            <div x-show="imagePreview" class="mb-4">
                                <img :src="imagePreview" alt="Certificate Preview" class="w-full rounded-lg border border-gray-300">
                            </div>

                            {{-- Badges --}}
                            <div class="flex flex-wrap gap-2">
                                <template x-if="previewData && previewData.status === 'completed'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✓ Completed
                                    </span>
                                </template>
                                <template x-if="previewData && previewData.status === 'in_progress'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        ⏳ In Progress
                                    </span>
                                </template>
                                <template x-if="previewData && previewData.level">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800': previewData.level === 'Beginner',
                                            'bg-orange-100 text-orange-800': previewData.level === 'Intermediate',
                                            'bg-red-100 text-red-800': previewData.level === 'Advanced'
                                        }"
                                        x-text="previewData.level">
                                    </span>
                                </template>
                                <template x-if="previewData && previewData.learning_hours">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        ⏱ <span x-text="previewData.learning_hours"></span> hrs
                                    </span>
                                </template>
                            </div>

                            {{-- Title --}}
                            <div x-show="previewData && previewData.title">
                                <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="(previewData && previewData.title) ? previewData.title : 'Untitled'"></h3>
                            </div>

                            {{-- Provider & Issued By --}}
                            <div class="text-sm text-gray-600 space-y-1" x-show="previewData && (previewData.provider || previewData.issued_by)">
                                <template x-if="previewData && previewData.provider">
                                    <p><span class="font-semibold">Provider:</span> <span x-text="previewData.provider"></span></p>
                                </template>
                                <template x-if="previewData && previewData.issued_by">
                                    <p><span class="font-semibold">Issued By:</span> <span x-text="previewData.issued_by"></span></p>
                                </template>
                                <template x-if="previewData && previewData.issued_at">
                                    <p><span class="font-semibold">Issued:</span> <span x-text="previewData.issued_at"></span></p>
                                </template>
                                <template x-if="previewData && previewData.expiry_date">
                                    <p><span class="font-semibold">Expires:</span> <span x-text="previewData.expiry_date"></span></p>
                                </template>
                            </div>

                            {{-- Learning Outcomes --}}
                            <div x-show="previewData && previewData.learning_outcomes" class="text-sm">
                                <p class="font-semibold text-gray-700 mb-1">Learning Outcomes:</p>
                                <ul class="list-disc list-inside text-gray-600 space-y-1">
                                    <template x-for="(obj, index) in ((previewData && previewData.learning_outcomes ? previewData.learning_outcomes.split('\n').filter(o => o.trim()) : []) || [])" :key="index">
                                        <li x-text="obj.trim().replace(/^[•\-\*]\s*/, '')"></li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Reflection --}}
                            <div x-show="previewData && previewData.reflection" class="text-sm">
                                <p class="font-semibold text-gray-700 mb-1">Reflection:</p>
                                <p class="text-gray-600 leading-relaxed" x-text="previewData && previewData.reflection ? previewData.reflection : ''"></p>
                            </div>

                            {{-- Tags --}}
                            <div x-show="previewData && previewData.tags" class="flex flex-wrap gap-2">
                                <template x-for="(tag, index) in ((previewData && previewData.tags ? previewData.tags.split(',').filter(t => t.trim()) : []) || [])" :key="index">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700" x-text="tag.trim()"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection


