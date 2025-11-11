@extends('layouts.app')
@section('title', 'Create New Course')
@section('content')
    <script>
        window.courseCreateData = {
            sections: @js($sections->map(function($s) {
                return ['id' => $s->id, 'name' => $s->getTranslated('title'), 'title' => $s->getTranslated('title'), 'category_id' => $s->category_id, 'category_name' => $s->category->getTranslated('name')];
            })),
            selectedCategories: []
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
                    <h1 class="text-4xl font-bold mb-2">Create New Course</h1>
                    <p class="text-cyan-100 text-lg">Document your learning journey with comprehensive course details</p>
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
                            Basic Information
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <x-dual-language-input 
                            name="title" 
                            label="Title" 
                            placeholder="e.g., Complete Java Masterclass"
                            required="true"
                        />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-dual-language-input 
                                    name="provider" 
                                    label="Provider" 
                                    placeholder="e.g., Udemy, Coursera, Pluralsight"
                                    required="true"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Course URL <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="url" 
                                    name="course_url" 
                                    @input="previewData.course_url = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                    placeholder="https://udemy.com/course/java-masterclass"
                                    required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Instructor / Organization
                                </label>
                                <input 
                                    name="instructor_organization" 
                                    @input="previewData.instructor_organization = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                    placeholder="e.g., Tim Buchalka" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="status" 
                                    @change="previewData.status = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none"
                                    required>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="retired">Retired</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Difficulty
                                </label>
                                <select 
                                    name="difficulty" 
                                    @change="previewData.difficulty = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none">
                                    <option value="">Select difficulty</option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Estimated Hours
                                </label>
                                <input 
                                    name="estimated_hours" 
                                    @input="previewData.estimated_hours = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                    placeholder="e.g., 12-15 hours" />
                            </div>
                        </div>

                        <div x-show="previewData.status === 'in_progress'">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Completion % (0-100)
                            </label>
                            <input 
                                type="number" 
                                name="completion_percent" 
                                min="0"
                                max="100"
                                @input="previewData.completion_percent = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                placeholder="e.g., 75" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Issued At
                                </label>
                                <input 
                                    type="date" 
                                    name="issued_at" 
                                    @input="previewData.issued_at = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Completed At
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
                                    Credential ID
                                </label>
                                <input 
                                    name="credential_id" 
                                    @input="previewData.credential_id = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none font-mono text-sm" 
                                    placeholder="e.g., UC-12345678" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Verify URL
                                </label>
                                <input 
                                    type="url" 
                                    name="verify_url" 
                                    @input="previewData.verify_url = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200 transition-all outline-none" 
                                    placeholder="https://udemy.com/certificate/UC-12345678" />
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
                            Learning & Scope
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Prerequisites
                            </label>
                            <textarea 
                                name="prerequisites" 
                                @input="previewData.prerequisites = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none" 
                                placeholder="What you knew going in. e.g., Basic Python knowledge, familiarity with OOP concepts"></textarea>
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
                            Skills & Syllabus
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Key Skills (3-8 items, one per line)
                            </label>
                            <textarea 
                                name="key_skills" 
                                @input="previewData.key_skills = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="• Generics&#10;• Streams&#10;• JUnit&#10;• Lambda Expressions"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Enter one skill per line</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Module Outline (5-10 items, one per line)
                            </label>
                            <textarea 
                                name="module_outline" 
                                @input="previewData.module_outline = $event.target.value"
                                rows="8" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="Module 1: Introduction to Java&#10;Module 2: Object-Oriented Programming&#10;Module 3: Collections Framework"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Enter one module per line</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Assessments / Grading
                            </label>
                            <textarea 
                                name="assessments_grading" 
                                @input="previewData.assessments_grading = $event.target.value"
                                rows="4" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="Quizzes: 5 quizzes, average score 90%&#10;Labs: 3 hands-on projects&#10;Capstone: Final project (grade: A)"></textarea>
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
                            Evidence & Reproducibility
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Certificate / Proof Image
                            </label>
                            <input 
                                type="file" 
                                name="image" 
                                accept="image/*,.pdf"
                                @change="previewImage($event)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" />
                            <p class="text-xs text-gray-500 mt-1">Upload certificate or completion proof (PNG, JPG, or PDF)</p>
                            <div x-show="imagePreview" class="mt-4">
                                <img :src="imagePreview" alt="Preview" class="max-w-xs rounded-lg border border-gray-300">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Artifacts / Assignments (Links - one per line)
                            </label>
                            <textarea 
                                name="artifacts_assignments" 
                                @input="previewData.artifacts_assignments = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none font-mono text-sm" 
                                placeholder="https://github.com/user/java-course-project-1&#10;https://gist.github.com/user/abc123&#10;https://example.com/demo"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Links to repos, gists, reports, or demos</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Proof Completion URL
                            </label>
                            <input 
                                type="url" 
                                name="proof_completion_url" 
                                @input="previewData.proof_completion_url = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                placeholder="Public badge link or certificate URL" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Screenshots (optional)
                            </label>
                            <input 
                                type="file" 
                                name="screenshots[]" 
                                accept="image/*"
                                multiple
                                @change="previewScreenshots($event)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" />
                            <p class="text-xs text-gray-500 mt-1">Completion screen, gradebook, etc.</p>
                            <div x-show="screenshotPreviews.length > 0" class="mt-4 grid grid-cols-3 gap-4">
                                <template x-for="(preview, index) in screenshotPreviews" :key="index">
                                    <img :src="preview" alt="Screenshot preview" class="rounded-lg border border-gray-300">
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Highlight Project Title
                                </label>
                                <input 
                                    name="highlight_project_title" 
                                    @input="previewData.highlight_project_title = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                    placeholder="e.g., E-commerce API" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Highlight Project Goal (1 sentence)
                                </label>
                                <input 
                                    name="highlight_project_goal" 
                                    @input="previewData.highlight_project_goal = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                    placeholder="Built a RESTful API using Spring Boot" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Highlight Project Link
                            </label>
                            <input 
                                type="url" 
                                name="highlight_project_link" 
                                @input="previewData.highlight_project_link = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                placeholder="https://github.com/user/project" />
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
                            Reflection & Impact
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <x-dual-language-input 
                            name="takeaways" 
                            label="Takeaways (2-4 sentences)" 
                            rows="4"
                            placeholder="What changed in how you work. e.g., Now I use Streams API for all collection operations..."
                        />

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Applied In
                            </label>
                            <textarea 
                                name="applied_in" 
                                @input="previewData.applied_in = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none resize-none" 
                                placeholder="Where you used it (project or job task) with link. e.g., Used Streams in AmaKo backend: https://github.com/user/amako"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Next Actions
                            </label>
                            <textarea 
                                name="next_actions" 
                                @input="previewData.next_actions = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none resize-none" 
                                placeholder="Precise follow-ups. e.g., Implement Streams in AmaKo inventory sync"></textarea>
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
                                    <option value="{{ $section->id }}">{{ $section->category->getTranslated('name') }} → {{ $section->getTranslated('title') }}</option>
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
                                placeholder="java, streams, spring boot" />
                            <p class="text-xs text-gray-500 mt-1">Tags are automatically normalized (lowercase, trimmed, max 5)</p>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.courses.index') }}" class="px-6 py-3 text-gray-700 font-semibold rounded-lg border-2 border-gray-300 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                        Create Course
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
                                <img :src="imagePreview" alt="Course Preview" class="w-full rounded-lg border border-gray-300">
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
                                <template x-if="previewData.status === 'retired'">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        Retired
                                    </span>
                                </template>
                                <template x-if="previewData.difficulty">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800': previewData.difficulty === 'Beginner',
                                            'bg-orange-100 text-orange-800': previewData.difficulty === 'Intermediate',
                                            'bg-red-100 text-red-800': previewData.difficulty === 'Advanced'
                                        }"
                                        x-text="previewData.difficulty">
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
                                <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="previewData.title || 'Untitled'"></h3>
                            </div>

                            {{-- Provider & Course URL --}}
                            <div class="text-sm text-gray-600 space-y-1" x-show="previewData.provider || previewData.course_url">
                                <template x-if="previewData.provider">
                                    <p><span class="font-semibold">Provider:</span> <span x-text="previewData.provider"></span></p>
                                </template>
                                <template x-if="previewData.instructor_organization">
                                    <p><span class="font-semibold">Instructor:</span> <span x-text="previewData.instructor_organization"></span></p>
                                </template>
                                <template x-if="previewData.completed_at">
                                    <p><span class="font-semibold">Completed:</span> <span x-text="previewData.completed_at"></span></p>
                                </template>
                            </div>

                            {{-- Key Skills --}}
                            <div x-show="previewData.key_skills" class="text-sm">
                                <p class="font-semibold text-gray-700 mb-1">Key Skills:</p>
                                <ul class="list-disc list-inside text-gray-600 space-y-1">
                                    <template x-for="(skill, index) in (previewData.key_skills?.split('\n').filter(s => s.trim()) || [])" :key="index">
                                        <li x-text="skill.trim().replace(/^[•\-\*]\s*/, '')"></li>
                                    </template>
                                </ul>
                        </div>

                            {{-- Takeaways --}}
                            <div x-show="previewData.takeaways" class="text-sm">
                                <p class="font-semibold text-gray-700 mb-1">Takeaways:</p>
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
