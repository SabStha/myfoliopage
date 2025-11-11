@extends('layouts.app')
@section('title', 'Create New Certificate')
@section('content')
    <script>
        window.certificateCreateData = {
            sections: @js($sections->map(function($s) {
                return ['id' => $s->id, 'name' => $s->getTranslated('title'), 'title' => $s->getTranslated('title'), 'category_id' => $s->category_id, 'category_name' => $s->category->getTranslated('name')];
            })),
            selectedCategories: []
        };
        
        // Register Alpine.js component data - works for both normal page load and modal load
        window.registerCertificateCreateComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                try {
                    window.Alpine.data('certificateCreate', () => ({
                        sections: window.certificateCreateData?.sections || [],
                        selectedCategories: window.certificateCreateData?.selectedCategories || [],
                        showNewSectionForm: false,
                        newSectionName: '',
                        newSectionCategoryId: '',
                        newSectionDescription: '',
                        loading: false,
                        imagePreview: null,
                        previewData: {
                            title: '',
                            provider: '',
                            issued_by: '',
                            credential_id: '',
                            verify_url: '',
                            issued_at: '',
                            expiry_date: '',
                            has_expiry: false,
                            level: '',
                            learning_hours: '',
                            learning_outcomes: '',
                            reflection: '',
                            status: 'completed',
                            tags: ''
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
                                        category_name: data.section.category.name || data.section.category.slug
                                    });
                                    const select = document.querySelector('select[name=\'sections[]\']');
                                    if (select) {
                                        const option = document.createElement('option');
                                        option.value = data.section.id;
                                        option.textContent = (data.section.category.name || data.section.category.slug) + ' → ' + sectionTitle;
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
        
        // Register immediately if Alpine is already loaded (for modal loads)
        if (window.Alpine && window.Alpine.data) {
            window.registerCertificateCreateComponent();
        } else {
            // Otherwise wait for Alpine to initialize (for normal page loads)
            document.addEventListener('alpine:init', window.registerCertificateCreateComponent);
        }
    </script>

    {{-- Hero Header --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Create New Certificate</h1>
                    <p class="text-blue-100 text-lg">Document your achievements with comprehensive verification details</p>
                </div>
                <div class="hidden md:block">
                    <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-data="certificateCreate()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <form method="POST" action="{{ route('admin.certificates.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            
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
                            placeholder="e.g., AWS Certified Solutions Architect"
                            required="true"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-dual-language-input 
                                    name="provider" 
                                    label="Provider" 
                                    placeholder="e.g., AWS, Coursera, TryHackMe"
                                    required="true"
                                    @input="previewData.provider = $event.target.value"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Issued By
                                </label>
                                <input 
                                    name="issued_by" 
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
                                x-model="previewData.has_expiry"
                                class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <label for="has_expiry" class="text-sm font-semibold text-gray-700">Certificate has expiration date</label>
                        </div>

                        <div x-show="previewData.has_expiry">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Expiry Date
                            </label>
                            <input 
                                type="date" 
                                name="expiry_date" 
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
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
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
                                    @input="previewData.learning_hours = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                    placeholder="e.g., 12" />
                            </div>
                        </div>

                        <x-dual-language-input 
                            name="learning_outcomes" 
                            label="Learning Outcomes / Key Topics (one per line)" 
                            rows="6"
                            placeholder="• IAM&#10;• EC2&#10;• S3&#10;• VPC basics"
                        />
                        <p class="text-xs text-gray-500 mt-1">Enter one topic per line</p>

                        <x-dual-language-input 
                            name="reflection" 
                            label="Reflection / Impact (2-4 sentences)" 
                            rows="4"
                            placeholder="How you applied this knowledge. e.g., Used VPC knowledge to deploy AmaKo staging server securely."
                        />

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Status
                            </label>
                            <select 
                                name="status" 
                                @change="previewData.status = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
                                <option value="completed">Completed</option>
                                <option value="in_progress">In Progress</option>
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
                                    <option value="{{ $project->id }}">{{ $project->title }}</option>
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
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none" 
                                size="5">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->getTranslated('name') ?: $category->slug }}</option>
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
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none" 
                                size="5">
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->category->getTranslated('name') ?: $section->category->slug }} → {{ $section->getTranslated('title') ?: $section->slug }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple sections</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tags (max 5, comma separated)
                            </label>
                            <input 
                                name="tags" 
                                @input="previewData.tags = normalizeTags($event.target.value)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none" 
                                placeholder="cloud, security, aws" />
                            <p class="text-xs text-gray-500 mt-1">Tags are automatically normalized (lowercase, trimmed, max 5)</p>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.certificates.index') }}" class="px-6 py-3 text-gray-700 font-semibold rounded-lg border-2 border-gray-300 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                        Create Certificate
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
                                <template x-if="previewData.status === 'completed'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✓ Completed
                                    </span>
                                </template>
                                <template x-if="previewData.status === 'in_progress'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        ⏳ In Progress
                                    </span>
                                </template>
                                <template x-if="previewData.level">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800': previewData.level === 'Beginner',
                                            'bg-orange-100 text-orange-800': previewData.level === 'Intermediate',
                                            'bg-red-100 text-red-800': previewData.level === 'Advanced'
                                        }"
                                        x-text="previewData.level">
                                    </span>
                                </template>
                                <template x-if="previewData.learning_hours">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        ⏱ <span x-text="previewData.learning_hours"></span> hrs
                                    </span>
                                </template>
                            </div>

                            {{-- Title --}}
                            <div x-show="previewData.title">
                                <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="previewData.title || 'Untitled'"></h3>
                            </div>

                            {{-- Provider & Issued By --}}
                            <div class="text-sm text-gray-600 space-y-1" x-show="previewData.provider || previewData.issued_by">
                                <template x-if="previewData.provider">
                                    <p><span class="font-semibold">Provider:</span> <span x-text="previewData.provider"></span></p>
                                </template>
                                <template x-if="previewData.issued_by">
                                    <p><span class="font-semibold">Issued By:</span> <span x-text="previewData.issued_by"></span></p>
                                </template>
                                <template x-if="previewData.issued_at">
                                    <p><span class="font-semibold">Issued:</span> <span x-text="previewData.issued_at"></span></p>
                                </template>
                                <template x-if="previewData.expiry_date">
                                    <p><span class="font-semibold">Expires:</span> <span x-text="previewData.expiry_date"></span></p>
                                </template>
                            </div>

                            {{-- Learning Outcomes --}}
                            <div x-show="previewData.learning_outcomes" class="text-sm">
                                <p class="font-semibold text-gray-700 mb-1">Learning Outcomes:</p>
                                <ul class="list-disc list-inside text-gray-600 space-y-1">
                                    <template x-for="(obj, index) in (previewData.learning_outcomes?.split('\n').filter(o => o.trim()) || [])" :key="index">
                                        <li x-text="obj.trim().replace(/^[•\-\*]\s*/, '')"></li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Reflection --}}
                            <div x-show="previewData.reflection" class="text-sm">
                                <p class="font-semibold text-gray-700 mb-1">Reflection:</p>
                                <p class="text-gray-600 leading-relaxed" x-text="previewData.reflection"></p>
                            </div>

                            {{-- Tags --}}
                            <div x-show="previewData.tags" class="flex flex-wrap gap-2">
                                <template x-for="(tag, index) in (previewData.tags?.split(',').filter(t => t.trim()) || [])" :key="index">
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
