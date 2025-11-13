@extends('layouts.app')
@section('title', 'Create New Course')
@section('content')
    <script>
        window.courseCreateData = {
            sections: @js($sections->map(function($s) {
                return ['id' => $s->id, 'name' => $s->getTranslated('title'), 'title' => $s->getTranslated('title'), 'category_id' => $s->category_id, 'category_name' => $s->category->getTranslated('name')];
            })),
            selectedCategories: [],
            translations: {
                provider: @json(__('app.admin.course.provider_label')),
                instructor: @json(__('app.admin.course.instructor_label')),
                completed: @json(__('app.admin.course.completed_label')),
                key_skills: @json(__('app.admin.course.key_skills_label')),
                takeaways: @json(__('app.admin.course.takeaways_label')),
                untitled: @json(__('app.admin.course.untitled')),
                status_completed: @json(__('app.admin.course.status_completed')),
                status_in_progress: @json(__('app.admin.course.status_in_progress')),
                status_retired: @json(__('app.admin.course.status_retired')),
                difficulty_beginner: @json(__('app.admin.course.difficulty_beginner')),
                difficulty_intermediate: @json(__('app.admin.course.difficulty_intermediate')),
                difficulty_advanced: @json(__('app.admin.course.difficulty_advanced'))
            }
        };
        
        // Register Alpine.js component data - works for both normal page load and modal load
        window.registerCourseCreateComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                try {
                    window.Alpine.data('courseCreate', () => ({
                        sections: window.courseCreateData?.sections || [],
                        selectedCategories: window.courseCreateData?.selectedCategories || [],
                        showNewSectionForm: false,
                        newSectionName: '',
                        newSectionCategoryId: '',
                        newSectionDescription: '',
                        loading: false,
                        imagePreview: null,
                        screenshotPreviews: [],
                        previewData: {
                            title: '',
                            provider: '',
                            course_url: '',
                            instructor_organization: '',
                            difficulty: '',
                            estimated_hours: '',
                            prerequisites: '',
                            key_skills: '',
                            module_outline: '',
                            assessments_grading: '',
                            artifacts_assignments: '',
                            highlight_project_title: '',
                            highlight_project_goal: '',
                            highlight_project_link: '',
                            proof_completion_url: '',
                            takeaways: '',
                            applied_in: '',
                            next_actions: '',
                            status: 'in_progress',
                            completion_percent: '',
                            tags: ''
                        },
                        translations: window.courseCreateData?.translations || {},
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
                        previewScreenshots(event) {
                            this.screenshotPreviews = [];
                            const files = Array.from(event.target.files);
                            files.forEach(file => {
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    this.screenshotPreviews.push(e.target.result);
                                };
                                reader.readAsDataURL(file);
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
            window.registerCourseCreateComponent();
        } else {
            // Otherwise wait for Alpine to initialize (for normal page loads)
            document.addEventListener('alpine:init', window.registerCourseCreateComponent);
        }
    </script>

    {{-- Hero Header --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-cyan-600 via-blue-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">{{ __('app.admin.course.create') }}</h1>
                    <p class="text-cyan-100 text-lg">{{ __('app.admin.course.create_description') }}</p>
                </div>
                <div class="hidden md:block">
                    <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-data="courseCreate()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            
            {{-- Left Column: Form (2/3 width) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Basic Information Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-cyan-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('app.admin.course.basic_information') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <x-dual-language-input 
                            name="title" 
                            label="{{ __('app.admin.course.title') }}" 
                            placeholder="{{ __('app.admin.course.title_placeholder') }}"
                            required="true"
                            x-on:input-updated="if ($event.detail.field === 'title') { previewData.title = $event.detail.value; }"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-dual-language-input 
                                    name="provider" 
                                    label="{{ __('app.admin.course.provider') }}" 
                                    placeholder="{{ __('app.admin.course.provider_placeholder') }}"
                                    required="true"
                                    x-on:input-updated="if ($event.detail.field === 'provider') { previewData.provider = $event.detail.value; }"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.course_url') }} <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="url" 
                                    name="course_url" 
                                    @input="previewData.course_url = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.course.course_url_placeholder') }}"
                                    required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.instructor_organization') }}
                                </label>
                                <input 
                                    name="instructor_organization" 
                                    @input="previewData.instructor_organization = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.course.instructor_organization_placeholder') }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.status') }} <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="status" 
                                    @change="previewData.status = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none"
                                    required>
                                    <option value="in_progress">{{ __('app.admin.course.status_in_progress') }}</option>
                                    <option value="completed">{{ __('app.admin.course.status_completed') }}</option>
                                    <option value="retired">{{ __('app.admin.course.status_retired') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.difficulty') }}
                                </label>
                                <select 
                                    name="difficulty" 
                                    @change="previewData.difficulty = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none">
                                    <option value="">{{ __('app.admin.course.difficulty_select') }}</option>
                                    <option value="Beginner">{{ __('app.admin.course.difficulty_beginner') }}</option>
                                    <option value="Intermediate">{{ __('app.admin.course.difficulty_intermediate') }}</option>
                                    <option value="Advanced">{{ __('app.admin.course.difficulty_advanced') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.estimated_hours') }}
                                </label>
                                <input 
                                    name="estimated_hours" 
                                    @input="previewData.estimated_hours = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.course.estimated_hours_placeholder') }}" />
                            </div>
                        </div>

                        <div x-show="previewData.status === 'in_progress'">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.completion_percent') }}
                            </label>
                            <input 
                                type="number" 
                                name="completion_percent" 
                                min="0"
                                max="100"
                                @input="previewData.completion_percent = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.course.completion_percent_placeholder') }}" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.issued_at') }}
                                </label>
                                <input 
                                    type="date" 
                                    name="issued_at" 
                                    @input="previewData.issued_at = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.completed_at') }}
                                </label>
                                <input 
                                    type="date" 
                                    name="completed_at" 
                                    @input="previewData.completed_at = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.credential_id') }}
                                </label>
                                <input 
                                    name="credential_id" 
                                    @input="previewData.credential_id = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none font-mono text-sm" 
                                    placeholder="{{ __('app.admin.course.credential_id_placeholder') }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.verify_url') }}
                                </label>
                                <input 
                                    type="url" 
                                    name="verify_url" 
                                    @input="previewData.verify_url = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.course.verify_url_placeholder') }}" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Learning & Scope Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('app.admin.course.learning_scope') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.prerequisites') }}
                            </label>
                            <textarea 
                                name="prerequisites" 
                                @input="previewData.prerequisites = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.course.prerequisites_placeholder') }}"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Skills & Syllabus Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            {{ __('app.admin.course.skills_syllabus') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.key_skills') }}
                            </label>
                            <textarea 
                                name="key_skills" 
                                @input="previewData.key_skills = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.course.key_skills_placeholder') }}"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.course.key_skills_hint') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.module_outline') }}
                            </label>
                            <textarea 
                                name="module_outline" 
                                @input="previewData.module_outline = $event.target.value"
                                rows="8" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.course.module_outline_placeholder') }}"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.course.module_outline_hint') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.assessments_grading') }}
                            </label>
                            <textarea 
                                name="assessments_grading" 
                                @input="previewData.assessments_grading = $event.target.value"
                                rows="4" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.course.assessments_grading_placeholder') }}"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Evidence & Reproducibility Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ __('app.admin.course.evidence_reproducibility') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.certificate_proof_image') }}
                            </label>
                            <input 
                                type="file" 
                                name="image" 
                                accept="image/*"
                                @change="previewImage($event)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" />
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.course.certificate_proof_image_hint') }}</p>
                            <div x-show="imagePreview" class="mt-4">
                                <img :src="imagePreview" alt="Preview" class="max-w-xs rounded-lg border border-gray-300">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.artifacts_assignments') }}
                            </label>
                            <textarea 
                                name="artifacts_assignments" 
                                @input="previewData.artifacts_assignments = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none font-mono text-sm" 
                                placeholder="{{ __('app.admin.course.artifacts_assignments_placeholder') }}"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.course.artifacts_assignments_hint') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.proof_completion_url') }}
                            </label>
                            <input 
                                type="url" 
                                name="proof_completion_url" 
                                @input="previewData.proof_completion_url = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.course.proof_completion_url_placeholder') }}" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.screenshots') }}
                            </label>
                            <input 
                                type="file" 
                                name="screenshots[]" 
                                accept="image/*"
                                multiple
                                @change="previewScreenshots($event)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" />
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.course.screenshots_hint') }}</p>
                            <div x-show="screenshotPreviews.length > 0" class="mt-4 grid grid-cols-3 gap-4">
                                <template x-for="(preview, index) in screenshotPreviews" :key="index">
                                    <img :src="preview" alt="Screenshot preview" class="rounded-lg border border-gray-300">
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.highlight_project_title') }}
                                </label>
                                <input 
                                    name="highlight_project_title" 
                                    @input="previewData.highlight_project_title = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.course.highlight_project_title_placeholder') }}" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.course.highlight_project_goal') }}
                                </label>
                                <input 
                                    name="highlight_project_goal" 
                                    @input="previewData.highlight_project_goal = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.course.highlight_project_goal_placeholder') }}" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.highlight_project_link') }}
                            </label>
                            <input 
                                type="url" 
                                name="highlight_project_link" 
                                @input="previewData.highlight_project_link = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.course.highlight_project_link_placeholder') }}" />
                        </div>
                    </div>
                </div>

                {{-- Reflection & Impact Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            {{ __('app.admin.course.reflection_impact') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <x-dual-language-input 
                            name="takeaways" 
                            label="{{ __('app.admin.course.takeaways') }}" 
                            rows="4"
                            placeholder="{{ __('app.admin.course.takeaways_placeholder') }}"
                        />

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.applied_in') }}
                            </label>
                            <textarea 
                                name="applied_in" 
                                @input="previewData.applied_in = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.course.applied_in_placeholder') }}"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.next_actions') }}
                            </label>
                            <textarea 
                                name="next_actions" 
                                @input="previewData.next_actions = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.course.next_actions_placeholder') }}"></textarea>
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
                            {{ __('app.admin.course.traceability_portfolio') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.categories') }}
                            </label>
                            <select 
                                name="categories[]" 
                                multiple 
                                @change="handleCategoryChange($event)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none" 
                                size="5">
                                @forelse($categories ?? [] as $category)
                                    @php
                                        $catNameRaw = $category->getTranslated('name');
                                        $catName = is_string($catNameRaw) ? $catNameRaw : ($category->slug ?? 'Unknown');
                                    @endphp
                                    <option value="{{ $category->id }}">{{ $catName }}</option>
                                @empty
                                    <option value="" disabled>No categories available. Please create categories first.</option>
                                @endforelse
                            </select>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.course.categories_hint') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.sections') }}
                            </label>
                            <select 
                                name="sections[]" 
                                multiple 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none" 
                                size="5">
                                @forelse($sections ?? [] as $section)
                                    @php
                                        $categoryName = 'Unknown';
                                        if ($section->category && is_object($section->category)) {
                                            try {
                                                $catName = $section->category->getTranslated('name');
                                                $categoryName = is_string($catName) && !empty($catName) ? $catName : (is_string($section->category->slug) ? $section->category->slug : 'Unknown');
                                            } catch (\Exception $e) {
                                                if (isset($section->category->slug) && is_string($section->category->slug)) {
                                                    $categoryName = $section->category->slug;
                                                }
                                            }
                                        }
                                        $sectionTitleRaw = $section->getTranslated('title');
                                        $sectionTitle = is_string($sectionTitleRaw) && !empty($sectionTitleRaw) ? $sectionTitleRaw : (is_string($section->slug) ? $section->slug : 'Untitled');
                                    @endphp
                                    <option value="{{ $section->id }}">{{ $categoryName }} → {{ $sectionTitle }}</option>
                                @empty
                                    <option value="" disabled>No sections available. Please create sections first.</option>
                                @endforelse
                            </select>
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.course.sections_hint') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.course.tags') }}
                            </label>
                            <input 
                                name="tags" 
                                @input="previewData.tags = normalizeTags($event.target.value)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.course.tags_placeholder') }}" />
                            <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.course.tags_hint') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.courses.index') }}" class="px-6 py-3 text-gray-700 font-semibold rounded-lg border-2 border-gray-300 hover:bg-gray-50 transition-colors">
                        {{ __('app.admin.course.cancel') }}
                    </a>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                        {{ __('app.admin.course.create_button') }}
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
                                {{ __('app.admin.course.live_preview') }}
                            </h3>
                        </div>
                        <div class="p-4 space-y-4">
                            {{-- Image Preview --}}
                            <div x-show="imagePreview" class="mb-4">
                                <img :src="imagePreview" alt="Course Preview" class="w-full rounded-lg border border-gray-300">
                            </div>

                            {{-- Badges --}}
                            <div class="flex flex-wrap gap-2">
                                <template x-if="previewData.status === 'completed'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✓ <span x-text="translations.status_completed || 'Completed'"></span>
                                    </span>
                                </template>
                                <template x-if="previewData.status === 'in_progress'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        ⏳ <span x-text="translations.status_in_progress || 'In Progress'"></span>
                                    </span>
                                </template>
                                <template x-if="previewData.status === 'retired'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        <span x-text="translations.status_retired || 'Retired'"></span>
                                    </span>
                                </template>
                                <template x-if="previewData.difficulty">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800': previewData.difficulty === 'Beginner',
                                            'bg-orange-100 text-orange-800': previewData.difficulty === 'Intermediate',
                                            'bg-red-100 text-red-800': previewData.difficulty === 'Advanced'
                                        }"
                                        x-text="previewData.difficulty === 'Beginner' ? (translations.difficulty_beginner || 'Beginner') : (previewData.difficulty === 'Intermediate' ? (translations.difficulty_intermediate || 'Intermediate') : (previewData.difficulty === 'Advanced' ? (translations.difficulty_advanced || 'Advanced') : previewData.difficulty))">
                                    </span>
                                </template>
                                <template x-if="previewData.completion_percent && previewData.status === 'in_progress'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        <span x-text="previewData.completion_percent"></span>%
                                    </span>
                                </template>
                                <template x-if="previewData.estimated_hours">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        ⏱ <span x-text="previewData.estimated_hours"></span>
                                    </span>
                                </template>
                            </div>

                            {{-- Title --}}
                            <div x-show="previewData.title">
                                <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="previewData.title || translations.untitled || 'Untitled'"></h3>
                            </div>

                            {{-- Provider & Course URL --}}
                            <div class="text-sm text-gray-600 space-y-1" x-show="previewData.provider || previewData.course_url">
                                <template x-if="previewData.provider">
                                    <p><span class="font-semibold" x-text="translations.provider || 'Provider:'"></span> <span x-text="previewData.provider"></span></p>
                                </template>
                                <template x-if="previewData.instructor_organization">
                                    <p><span class="font-semibold" x-text="translations.instructor || 'Instructor:'"></span> <span x-text="previewData.instructor_organization"></span></p>
                                </template>
                                <template x-if="previewData.completed_at">
                                    <p><span class="font-semibold" x-text="translations.completed || 'Completed:'"></span> <span x-text="previewData.completed_at"></span></p>
                                </template>
                            </div>

                            {{-- Key Skills --}}
                            <div x-show="previewData.key_skills" class="text-sm">
                                <p class="font-semibold text-gray-700 mb-1" x-text="translations.key_skills || 'Key Skills:'"></p>
                                <ul class="list-disc list-inside text-gray-600 space-y-1">
                                    <template x-for="(skill, index) in (previewData.key_skills?.split('\n').filter(s => s.trim()) || [])" :key="index">
                                        <li x-text="skill.trim().replace(/^[•\-\*]\s*/, '')"></li>
                                    </template>
                                </ul>
                        </div>

                            {{-- Takeaways --}}
                            <div x-show="previewData.takeaways" class="text-sm">
                                <p class="font-semibold text-gray-700 mb-1" x-text="translations.takeaways || 'Takeaways:'"></p>
                                <p class="text-gray-600 leading-relaxed" x-text="previewData.takeaways"></p>
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
