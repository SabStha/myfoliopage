@extends('layouts.app')
@section('title', 'Edit Code Summary')
@section('content')
    <script>
        window.codeSummaryEditData = {
            sections: @js($sections->map(function($s) {
                return ['id' => $s->id, 'name' => $s->title, 'title' => $s->title, 'category_id' => $s->category_id, 'category_name' => $s->category->name];
            })),
            selectedCategories: @js($codeSummary->categories->pluck('id')->toArray())
        };
        
        // Register Alpine.js component data - works for both normal page load and modal load
        window.registerCodeSummaryEditComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                try {
                    window.Alpine.data('codeSummaryEdit', () => ({
                        sections: window.codeSummaryEditData?.sections || [],
                        selectedCategories: window.codeSummaryEditData?.selectedCategories || [],
                        showNewSectionForm: false,
                        newSectionName: '',
                        newSectionCategoryId: '',
                        newSectionDescription: '',
                        loading: false,
                        autoSlug: false,
                        previewData: {
                            title: @js(old('title', $codeSummary->title ?? '')),
                            summary: @js(old('summary', $codeSummary->summary ?? '')),
                            code: @js(old('code', $codeSummary->code ?? '')),
                            language: @js(old('language', $codeSummary->language ?? '')),
                            problem_statement: @js(old('problem_statement', $codeSummary->problem_statement ?? '')),
                            learning_goal: @js(old('learning_goal', $codeSummary->learning_goal ?? '')),
                            use_case: @js(old('use_case', $codeSummary->use_case ?? '')),
                            how_to_run: @js(old('how_to_run', $codeSummary->how_to_run ?? '')),
                            expected_output: @js(old('expected_output', $codeSummary->expected_output ?? '')),
                            dependencies: @js(old('dependencies', $codeSummary->dependencies ?? '')),
                            test_status: @js(old('test_status', $codeSummary->test_status ?? '')),
                            complexity_notes: @js(old('complexity_notes', $codeSummary->complexity_notes ?? '')),
                            security_notes: @js(old('security_notes', $codeSummary->security_notes ?? '')),
                            reflection: @js(old('reflection', $codeSummary->reflection ?? '')),
                            commit_sha: @js(old('commit_sha', $codeSummary->commit_sha ?? '')),
                            license: @js(old('license', $codeSummary->license ?? '')),
                            file_path_repo: @js(old('file_path_repo', $codeSummary->file_path_repo ?? '')),
                            framework: @js(old('framework', $codeSummary->framework ?? '')),
                            difficulty: @js(old('difficulty', $codeSummary->difficulty ?? '')),
                            time_spent: @js(old('time_spent', $codeSummary->time_spent ?? '')),
                            status: @js(old('status', $codeSummary->status ?? 'completed')),
                            tags: @js(old('tags', $codeSummary->tags->pluck('name')->implode(', ') ?? ''))
                        },
                        normalizeTags(tags) {
                            if (!tags) return '';
                            return tags.split(',')
                                .map(t => t.trim().toLowerCase())
                                .filter(t => t)
                                .slice(0, 5)
                                .join(', ');
                        },
                        generateSlug(title) {
                            if (this.autoSlug) {
                                const slug = title.toLowerCase()
                                    .trim()
                                    .replace(/[^\w\s-]/g, '')
                                    .replace(/[\s_-]+/g, '-')
                                    .replace(/^-+|-+$/g, '');
                                document.querySelector('input[name=\'slug\']').value = slug;
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
                        },
                        init() {
                            // Sync previewData with form field values on load
                            this.$nextTick(() => {
                                const form = this.$el.closest('form') || document.querySelector('form');
                                if (form) {
                                    const titleInput = form.querySelector('[name="title"]');
                                    if (titleInput) this.previewData.title = titleInput.value || '';
                                    const summaryTextarea = form.querySelector('[name="summary"]');
                                    if (summaryTextarea) this.previewData.summary = summaryTextarea.value || '';
                                    const codeTextarea = form.querySelector('[name="code"]');
                                    if (codeTextarea) this.previewData.code = codeTextarea.value || '';
                                    const languageInput = form.querySelector('[name="language"]');
                                    if (languageInput) this.previewData.language = languageInput.value || '';
                                    const problemStatementInput = form.querySelector('[name="problem_statement"]');
                                    if (problemStatementInput) this.previewData.problem_statement = problemStatementInput.value || '';
                                    const learningGoalTextarea = form.querySelector('[name="learning_goal"]');
                                    if (learningGoalTextarea) this.previewData.learning_goal = learningGoalTextarea.value || '';
                                    const useCaseTextarea = form.querySelector('[name="use_case"]');
                                    if (useCaseTextarea) this.previewData.use_case = useCaseTextarea.value || '';
                                    const howToRunTextarea = form.querySelector('[name="how_to_run"]');
                                    if (howToRunTextarea) this.previewData.how_to_run = howToRunTextarea.value || '';
                                    const expectedOutputTextarea = form.querySelector('[name="expected_output"]');
                                    if (expectedOutputTextarea) this.previewData.expected_output = expectedOutputTextarea.value || '';
                                    const dependenciesInput = form.querySelector('[name="dependencies"]');
                                    if (dependenciesInput) this.previewData.dependencies = dependenciesInput.value || '';
                                    const testStatusInput = form.querySelector('[name="test_status"]');
                                    if (testStatusInput) this.previewData.test_status = testStatusInput.value || '';
                                    const complexityNotesTextarea = form.querySelector('[name="complexity_notes"]');
                                    if (complexityNotesTextarea) this.previewData.complexity_notes = complexityNotesTextarea.value || '';
                                    const securityNotesTextarea = form.querySelector('[name="security_notes"]');
                                    if (securityNotesTextarea) this.previewData.security_notes = securityNotesTextarea.value || '';
                                    const reflectionTextarea = form.querySelector('[name="reflection"]');
                                    if (reflectionTextarea) this.previewData.reflection = reflectionTextarea.value || '';
                                    const commitShaInput = form.querySelector('[name="commit_sha"]');
                                    if (commitShaInput) this.previewData.commit_sha = commitShaInput.value || '';
                                    const licenseInput = form.querySelector('[name="license"]');
                                    if (licenseInput) this.previewData.license = licenseInput.value || '';
                                    const filePathRepoInput = form.querySelector('[name="file_path_repo"]');
                                    if (filePathRepoInput) this.previewData.file_path_repo = filePathRepoInput.value || '';
                                    const frameworkInput = form.querySelector('[name="framework"]');
                                    if (frameworkInput) this.previewData.framework = frameworkInput.value || '';
                                    const difficultySelect = form.querySelector('[name="difficulty"]');
                                    if (difficultySelect) this.previewData.difficulty = difficultySelect.value || '';
                                    const timeSpentInput = form.querySelector('[name="time_spent"]');
                                    if (timeSpentInput) this.previewData.time_spent = timeSpentInput.value || '';
                                    const statusSelect = form.querySelector('[name="status"]');
                                    if (statusSelect) this.previewData.status = statusSelect.value || 'completed';
                                    const tagsInput = form.querySelector('[name="tags"]');
                                    if (tagsInput) this.previewData.tags = tagsInput.value || '';
                                }
                            });
                        }
                    }));
                } catch (e) {
                    console.error('Error registering Alpine component:', e);
                }
            }
        };
        
        // Register immediately if Alpine is already loaded (for modal loads)
        if (window.Alpine && window.Alpine.data) {
            window.registerCodeSummaryEditComponent();
        } else {
            // Otherwise wait for Alpine to initialize (for normal page loads)
            document.addEventListener('alpine:init', window.registerCodeSummaryEditComponent);
        }
    </script>

    {{-- Hero Header --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Edit Code Summary</h1>
                    <p class="text-blue-100 text-lg">Update your code documentation and proof</p>
                </div>
                <div class="hidden md:block">
                    <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Two Column Layout: Form Left, Preview Right --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="codeSummaryEdit()">
            {{-- Form Column (2/3 width) --}}
            <div class="lg:col-span-2 space-y-6">
                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.code-summaries.update', $codeSummary) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Basic Information Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Basic Information
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Title <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    name="title" 
                                    value="{{ old('title', $codeSummary->title) }}"
                                    @input="previewData.title = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="Enter code summary title" 
                                    required 
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Slug <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    name="slug" 
                                    value="{{ old('slug', $codeSummary->slug) }}"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none font-mono text-sm" 
                                    placeholder="url-friendly-slug"
                                    required 
                                />
                                <p class="text-xs text-gray-500 mt-1">URL-friendly identifier</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Summary <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="summary" 
                                    @input="previewData.summary = $event.target.value"
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none resize-none" 
                                    placeholder="Brief summary of what the code does..." 
                                    required
                                >{{ old('summary', $codeSummary->summary) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Language
                                    </label>
                                    <input 
                                        name="language" 
                                        value="{{ old('language', $codeSummary->language) }}"
                                        @input="previewData.language = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                        placeholder="PHP, JavaScript, Python..." 
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Framework / Library
                                    </label>
                                    <input 
                                        name="framework" 
                                        value="{{ old('framework', $codeSummary->framework) }}"
                                        @input="previewData.framework = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                        placeholder="Laravel, React, Django..." 
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Code
                                </label>
                                <textarea 
                                    name="code" 
                                    @input="previewData.code = $event.target.value"
                                    rows="15" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none resize-none font-mono text-sm" 
                                    placeholder="Paste your code here..."
                                >{{ old('code', $codeSummary->code) }}</textarea>
                                <p class="text-xs text-gray-500 mt-2">If code is empty, Expected Output becomes required</p>
                            </div>
                        </div>
                    </div>

                    {{-- Context & Purpose Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                Context & Purpose
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Problem Statement
                                </label>
                                <input 
                                    name="problem_statement" 
                                    value="{{ old('problem_statement', $codeSummary->problem_statement) }}"
                                    @input="previewData.problem_statement = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none" 
                                    placeholder="One-sentence description of what the code solves" 
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Learning Goal / Objective
                                </label>
                                <textarea 
                                    name="learning_goal" 
                                    @input="previewData.learning_goal = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none" 
                                    placeholder="What skill or concept you practiced"
                                >{{ old('learning_goal', $codeSummary->learning_goal) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Use Case / Scenario
                                </label>
                                <textarea 
                                    name="use_case" 
                                    @input="previewData.use_case = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none" 
                                    placeholder="Where this code is used (mini-project, exercise, bug fix)"
                                >{{ old('use_case', $codeSummary->use_case) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Proof & Reproducibility Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Proof & Reproducibility
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    How to Run / Test <span class="text-red-500">*</span> (if code is empty)
                                </label>
                                <textarea 
                                    name="how_to_run" 
                                    @input="previewData.how_to_run = $event.target.value"
                                    rows="6" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none font-mono text-sm" 
                                    placeholder="Exact commands or environment setup&#10;Example:&#10;composer install&#10;php artisan test --filter=RateLimiterTest"
                                >{{ old('how_to_run', $codeSummary->how_to_run) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Expected Output / Evidence <span class="text-red-500">*</span> (if code is empty)
                                </label>
                                <textarea 
                                    name="expected_output" 
                                    @input="previewData.expected_output = $event.target.value"
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none" 
                                    placeholder="Result screenshot, console output, or test report"
                                >{{ old('expected_output', $codeSummary->expected_output) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Dependencies / Environment
                                </label>
                                <input 
                                    name="dependencies" 
                                    value="{{ old('dependencies', $codeSummary->dependencies) }}"
                                    @input="previewData.dependencies = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                    placeholder="PHP 8.2, Laravel 11, MySQL 8" 
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Test Status / Lint Status
                                </label>
                                <input 
                                    name="test_status" 
                                    value="{{ old('test_status', $codeSummary->test_status) }}"
                                    @input="previewData.test_status = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                    placeholder="10/10 tests passed, no warnings" 
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Evaluation & Reflection Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                Evaluation & Reflection
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Complexity / Performance Notes
                                </label>
                                <textarea 
                                    name="complexity_notes" 
                                    @input="previewData.complexity_notes = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="O(n), runtime, memory, or benchmark summary"
                                >{{ old('complexity_notes', $codeSummary->complexity_notes) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Security / Edge Cases
                                </label>
                                <textarea 
                                    name="security_notes" 
                                    @input="previewData.security_notes = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="Potential risks, input validation, or error handling"
                                >{{ old('security_notes', $codeSummary->security_notes) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Reflection / Takeaways
                                </label>
                                <textarea 
                                    name="reflection" 
                                    @input="previewData.reflection = $event.target.value"
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="What worked, what you'd improve"
                                >{{ old('reflection', $codeSummary->reflection) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Traceability Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-teal-50 to-cyan-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Traceability
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Repository Commit SHA
                                    </label>
                                    <input 
                                        name="commit_sha" 
                                        value="{{ old('commit_sha', $codeSummary->commit_sha) }}"
                                        @input="previewData.commit_sha = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none font-mono text-sm" 
                                        placeholder="abc123def456..." 
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        License / Ownership
                                    </label>
                                    <input 
                                        name="license" 
                                        value="{{ old('license', $codeSummary->license) }}"
                                        @input="previewData.license = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                        placeholder="MIT, Apache 2.0, Proprietary..." 
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    File Path in Repo
                                </label>
                                <input 
                                    name="file_path_repo" 
                                    value="{{ old('file_path_repo', $codeSummary->file_path_repo) }}"
                                    @input="previewData.file_path_repo = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none font-mono text-sm" 
                                    placeholder="src/Controllers/RateLimiter.php" 
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Repository URL
                                </label>
                                <input 
                                    name="repository_url" 
                                    type="url"
                                    value="{{ old('repository_url', $codeSummary->repository_url) }}"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                    placeholder="https://github.com/username/repo" 
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    File Path (Local)
                                </label>
                                <input 
                                    name="file_path" 
                                    value="{{ old('file_path', $codeSummary->file_path) }}"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none font-mono text-sm" 
                                    placeholder="app/Http/Controllers/..." 
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Metadata Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Metadata
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Difficulty Level
                                    </label>
                                    <select 
                                        name="difficulty" 
                                        @change="previewData.difficulty = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none"
                                    >
                                        <option value="">Select difficulty...</option>
                                        <option value="Beginner" {{ old('difficulty', $codeSummary->difficulty) === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="Intermediate" {{ old('difficulty', $codeSummary->difficulty) === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="Advanced" {{ old('difficulty', $codeSummary->difficulty) === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Time Spent (minutes)
                                    </label>
                                    <input 
                                        name="time_spent" 
                                        type="number"
                                        value="{{ old('time_spent', $codeSummary->time_spent) }}"
                                        min="0"
                                        @input="previewData.time_spent = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none" 
                                        placeholder="e.g., 45" 
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Status
                                    </label>
                                    <select 
                                        name="status" 
                                        @change="previewData.status = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none"
                                    >
                                        <option value="completed" {{ old('status', $codeSummary->status ?? 'completed') === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="in_progress" {{ old('status', $codeSummary->status ?? 'completed') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Categories & Sections Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-teal-50 to-cyan-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Organization
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
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none min-h-[150px]"
                                >
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $codeSummary->categories->contains($category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-2">Hold Ctrl/Cmd to select multiple categories</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Sections (grouping within categories)
                                </label>
                                <select 
                                    name="sections[]" 
                                    multiple 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none min-h-[150px]"
                                >
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ $codeSummary->sections->contains($section->id) ? 'selected' : '' }}>{{ $section->category->name }} → {{ $section->title }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-2">Hold Ctrl/Cmd to select multiple sections.</p>
                                
                                <div class="mt-4">
                                    <button 
                                        type="button" 
                                        @click="showNewSectionForm = !showNewSectionForm" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-teal-600 hover:text-teal-700 hover:bg-teal-50 rounded-lg transition-colors border border-teal-200"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add New Section
                                    </button>
                                </div>
                                
                                <div x-show="showNewSectionForm" x-cloak x-transition class="mt-4 p-6 bg-gradient-to-br from-teal-50 to-cyan-50 rounded-xl border-2 border-teal-200">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Create New Section
                                    </h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                            <select 
                                                x-model="newSectionCategoryId" 
                                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                                required
                                            >
                                                <option value="">Select a category...</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Section Name</label>
                                            <input 
                                                type="text" 
                                                x-model="newSectionName" 
                                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                                placeholder="e.g., Lesson 1" 
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description (optional)</label>
                                            <textarea 
                                                x-model="newSectionDescription" 
                                                rows="3" 
                                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none resize-none" 
                                                placeholder="Brief description..."
                                            ></textarea>
                                        </div>
                                        <div class="flex gap-3">
                                            <button 
                                                type="button" 
                                                @click="createNewSection()" 
                                                :disabled="loading" 
                                                class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-lg font-medium disabled:opacity-50 transition-all shadow-md hover:shadow-lg"
                                            >
                                                <span x-show="!loading">Create & Add</span>
                                                <span x-show="loading" class="flex items-center gap-2">
                                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Creating...
                                                </span>
                                            </button>
                                            <button 
                                                type="button" 
                                                @click="showNewSectionForm = false; newSectionName = ''; newSectionDescription = ''; newSectionCategoryId = '';" 
                                                class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-all"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tags (comma separated, max 5)
                                </label>
                                <input 
                                    name="tags" 
                                    value="{{ old('tags', $codeSummary->tags->pluck('name')->implode(', ')) }}"
                                    @input="previewData.tags = normalizeTags($event.target.value)"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                    placeholder="Laravel, PHP, Security, Best Practices" 
                                />
                                <p class="text-xs text-gray-500 mt-2">Tags will be trimmed, lowercased, and limited to 5</p>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <a 
                            href="{{ route('admin.code-summaries.index') }}" 
                            class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </a>
                        <button 
                            type="submit" 
                            class="px-8 py-3 text-sm font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg transition-all shadow-lg hover:shadow-xl"
                        >
                            Update Code Summary
                        </button>
                    </div>
                </form>
            </div>

            {{-- Preview Column (1/3 width) --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Live Preview
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            {{-- Badges Row --}}
                            <div class="flex flex-wrap gap-2" x-show="previewData && (previewData.status || previewData.difficulty || previewData.time_spent)">
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
                                <template x-if="previewData && previewData.difficulty">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-800': previewData.difficulty === 'Beginner',
                                            'bg-orange-100 text-orange-800': previewData.difficulty === 'Intermediate',
                                            'bg-red-100 text-red-800': previewData.difficulty === 'Advanced'
                                        }"
                                        x-text="previewData.difficulty">
                                    </span>
                                </template>
                                <template x-if="previewData && previewData.time_spent">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        ⏱ <span x-text="previewData.time_spent"></span> min
                                    </span>
                                </template>
                            </div>

                            {{-- Title --}}
                            <div x-show="previewData && previewData.title">
                                <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="(previewData && previewData.title) ? previewData.title : 'Code Summary Title'"></h3>
                            </div>

                            {{-- Summary --}}
                            <div x-show="previewData && previewData.summary">
                                <p class="text-sm text-gray-600 leading-relaxed mb-3" x-text="(previewData && previewData.summary) ? previewData.summary : ''"></p>
                            </div>

                            {{-- Language & Framework --}}
                            <div class="flex flex-wrap gap-2" x-show="previewData && (previewData.language || previewData.framework)">
                                <template x-if="previewData && previewData.language">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <span x-text="previewData.language"></span>
                                    </span>
                                </template>
                                <template x-if="previewData && previewData.framework">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                        <span x-text="previewData.framework"></span>
                                    </span>
                                </template>
                            </div>

                            {{-- Problem Statement --}}
                            <div x-show="previewData && previewData.problem_statement" class="p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                                <p class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-1">Problem</p>
                                <p class="text-sm text-gray-700" x-text="(previewData && previewData.problem_statement) ? previewData.problem_statement : ''"></p>
                            </div>

                            {{-- Code Preview --}}
                            <div x-show="previewData && previewData.code" class="bg-gray-900 rounded-lg overflow-hidden border-2 border-gray-800">
                                <div class="px-3 py-2 bg-gray-800 border-b border-gray-700 flex items-center gap-2">
                                    <div class="flex gap-1.5">
                                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    </div>
                                    <span class="text-xs text-gray-400 ml-2" x-text="(previewData && previewData.language) ? previewData.language : 'Code'"></span>
                                </div>
                                <pre class="p-3 overflow-x-auto max-h-40"><code class="text-gray-100 font-mono text-xs" x-text="(previewData && previewData.code) ? (previewData.code.substring(0, 200) + (previewData.code.length > 200 ? '...' : '')) : ''"></code></pre>
                            </div>

                            {{-- Tags --}}
                            <div x-show="previewData && previewData.tags" class="flex flex-wrap gap-2">
                                <template x-for="tag in ((previewData && previewData.tags ? previewData.tags.split(',').filter(t => t.trim()) : []) || [])" :key="tag">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-300" x-text="tag.trim()"></span>
                                </template>
                            </div>

                            {{-- Empty State --}}
                            <div x-show="!previewData || (!previewData.title && !previewData.summary)" class="text-center py-8 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                                <p class="text-sm">Preview will appear here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

