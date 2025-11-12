@extends('layouts.app')
@section('title', __('app.admin.code_summary.create'))
@section('content')
    <script>
        window.codeSummaryCreateData = {
            sections: @js($sections->map(function($s) {
                return ['id' => $s->id, 'name' => $s->getTranslated('title'), 'title' => $s->getTranslated('title'), 'category_id' => $s->category_id, 'category_name' => $s->category->getTranslated('name')];
            })),
            selectedCategories: [],
            translations: {
                completed: '{{ __('app.common.completed') }}',
                in_progress: '{{ __('app.common.in_progress') }}',
                problem: '{{ __('app.common.problem') }}',
                code: '{{ __('app.common.code') }}',
                code_summary_title: '{{ __('app.admin.code_summary.code_summary_title') }}',
                min: '{{ __('app.common.min') }}'
            }
        };
        
        // Register Alpine.js component data - works for both normal page load and modal load
        window.registerCodeSummaryCreateComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                // Always register/overwrite the component
                try {
                    window.Alpine.data('codeSummaryCreate', () => ({
                sections: window.codeSummaryCreateData?.sections || [],
                selectedCategories: window.codeSummaryCreateData?.selectedCategories || [],
                showNewSectionForm: false,
                newSectionName: '',
                newSectionCategoryId: '',
                newSectionDescription: '',
                loading: false,
                autoSlug: true,
                previewData: {
                    title: '',
                    summary: '',
                    code: '',
                    language: '',
                    problem_statement: '',
                    learning_goal: '',
                    use_case: '',
                    how_to_run: '',
                    expected_output: '',
                    dependencies: '',
                    test_status: '',
                    complexity_notes: '',
                    security_notes: '',
                    reflection: '',
                    commit_sha: '',
                    license: '',
                    file_path_repo: '',
                    framework: '',
                    difficulty: '',
                    time_spent: '',
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
            window.registerCodeSummaryCreateComponent();
        } else {
            // Otherwise wait for Alpine to initialize (for normal page loads)
            document.addEventListener('alpine:init', window.registerCodeSummaryCreateComponent);
        }
        
        // Alpine.js component for code summary form with auto-translation
        // Register globally so it's available for modal loads
        window.registerCodeSummaryTranslationComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                try {
                    window.Alpine.data('codeSummaryTranslationData', () => ({
                        titleLang: '{{ app()->getLocale() }}',
                        titleEn: '',
                        titleJa: '',
                        translatingTitle: false,
                        titleTranslateTimeout: null,
                        summaryLang: '{{ app()->getLocale() }}',
                        summaryEn: '',
                        summaryJa: '',
                        translatingSummary: false,
                        summaryTranslateTimeout: null,
                        async translateText(text, fromLang, toLang, field) {
                            if (!text || text.trim().length === 0) return;
                            
                            if (field === 'title') {
                                this.translatingTitle = true;
                            } else {
                                this.translatingSummary = true;
                            }
                            
                            try {
                                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                                const response = await fetch('/api/translate', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({
                                        text: text,
                                        from: fromLang,
                                        to: toLang
                                    })
                                });
                                
                                if (response.ok) {
                                    const data = await response.json();
                                    if (data.translated) {
                                        if (field === 'title') {
                                            if (toLang === 'en') {
                                                this.titleEn = data.translated;
                                                // Update preview
                                                if (window.Alpine && document.querySelector('[x-data*="codeSummaryCreate"]')) {
                                                    const codeSummaryCreate = Alpine.$data(document.querySelector('[x-data*="codeSummaryCreate"]'));
                                                    if (codeSummaryCreate && codeSummaryCreate.previewData) {
                                                        codeSummaryCreate.previewData.title = data.translated;
                                                    }
                                                }
                                            } else {
                                                this.titleJa = data.translated;
                                            }
                                        } else {
                                            if (toLang === 'en') {
                                                this.summaryEn = data.translated;
                                                // Update preview
                                                if (window.Alpine && document.querySelector('[x-data*="codeSummaryCreate"]')) {
                                                    const codeSummaryCreate = Alpine.$data(document.querySelector('[x-data*="codeSummaryCreate"]'));
                                                    if (codeSummaryCreate && codeSummaryCreate.previewData) {
                                                        codeSummaryCreate.previewData.summary = data.translated;
                                                    }
                                                }
                                            } else {
                                                this.summaryJa = data.translated;
                                            }
                                        }
                                    }
                                }
                            } catch (error) {
                                console.error('Translation error:', error);
                            } finally {
                                if (field === 'title') {
                                    this.translatingTitle = false;
                                } else {
                                    this.translatingSummary = false;
                                }
                            }
                        },
                        handleTitleInput(value, currentLang) {
                            if (currentLang === 'en') {
                                this.titleEn = value;
                                // Update preview
                                if (window.Alpine && document.querySelector('[x-data*="codeSummaryCreate"]')) {
                                    const codeSummaryCreate = Alpine.$data(document.querySelector('[x-data*="codeSummaryCreate"]'));
                                    if (codeSummaryCreate && codeSummaryCreate.previewData) {
                                        codeSummaryCreate.previewData.title = value;
                                    }
                                    if (codeSummaryCreate && codeSummaryCreate.generateSlug) {
                                        codeSummaryCreate.generateSlug(value);
                                    }
                                }
                                clearTimeout(this.titleTranslateTimeout);
                                this.titleTranslateTimeout = setTimeout(() => {
                                    if (value && value.trim().length > 0) {
                                        this.translateText(value, 'en', 'ja', 'title');
                                    }
                                }, 1000);
                            } else {
                                this.titleJa = value;
                                clearTimeout(this.titleTranslateTimeout);
                                this.titleTranslateTimeout = setTimeout(() => {
                                    if (value && value.trim().length > 0) {
                                        this.translateText(value, 'ja', 'en', 'title');
                                    }
                                }, 1000);
                            }
                        },
                        handleSummaryInput(value, currentLang) {
                            if (currentLang === 'en') {
                                this.summaryEn = value;
                                // Update preview
                                if (window.Alpine && document.querySelector('[x-data*="codeSummaryCreate"]')) {
                                    const codeSummaryCreate = Alpine.$data(document.querySelector('[x-data*="codeSummaryCreate"]'));
                                    if (codeSummaryCreate && codeSummaryCreate.previewData) {
                                        codeSummaryCreate.previewData.summary = value;
                                    }
                                }
                                clearTimeout(this.summaryTranslateTimeout);
                                this.summaryTranslateTimeout = setTimeout(() => {
                                    if (value && value.trim().length > 0) {
                                        this.translateText(value, 'en', 'ja', 'summary');
                                    }
                                }, 1000);
                            } else {
                                this.summaryJa = value;
                                clearTimeout(this.summaryTranslateTimeout);
                                this.summaryTranslateTimeout = setTimeout(() => {
                                    if (value && value.trim().length > 0) {
                                        this.translateText(value, 'ja', 'en', 'summary');
                                    }
                                }, 1000);
                            }
                        }
                    }));
                } catch (e) {
                    console.error('Error registering codeSummaryTranslationData component:', e);
                }
            }
        };
        
        // Register translation component immediately if Alpine is already loaded
        if (window.Alpine && window.Alpine.data) {
            window.registerCodeSummaryTranslationComponent();
        } else {
            document.addEventListener('alpine:init', window.registerCodeSummaryTranslationComponent);
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>

    {{-- Hero Header --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">{{ __('app.admin.code_summary.create') }}</h1>
                    <p class="text-blue-100 text-lg">{{ __('app.admin.code_summary.create_description') }}</p>
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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="codeSummaryCreate()">
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

            <form method="POST" action="{{ route('admin.code-summaries.store') }}" class="space-y-6">
                @csrf

                {{-- Basic Information Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('app.admin.code_summary.basic_information') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6" x-data="codeSummaryTranslationData()">
                        {{-- Title Field with Bilingual Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    {{ __('app.admin.code_summary.title') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <select x-model="titleLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="en">🇬🇧 English</option>
                                        <option value="ja">🇯🇵 日本語</option>
                                    </select>
                                    <span x-show="translatingTitle" class="text-xs text-gray-500 flex items-center gap-1" x-cloak>
                                        <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('app.common.translating') }}
                                    </span>
                                </div>
                            </div>
                            <div x-show="titleLang === 'en'" 
                                 x-transition:enter="transition ease-out duration-200" 
                                 x-transition:enter-start="opacity-0" 
                                 x-transition:enter-end="opacity-100"
                                 x-cloak>
                                <input 
                                    name="title[en]" 
                                    x-model="titleEn"
                                    @input="handleTitleInput($event.target.value, 'en')"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.code_summary.title_placeholder') }}"
                                    required />
                                <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_english_auto_translate') }}</p>
                            </div>
                            <div x-show="titleLang === 'ja'" 
                                 x-transition:enter="transition ease-out duration-200" 
                                 x-transition:enter-start="opacity-0" 
                                 x-transition:enter-end="opacity-100"
                                 x-cloak>
                                <input 
                                    name="title[ja]" 
                                    x-model="titleJa"
                                    @input="handleTitleInput($event.target.value, 'ja')"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.code_summary.title_placeholder') }}"
                                    required />
                                <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.code_summary.slug') }}
                                </label>
                                <input 
                                    name="slug" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.code_summary.slug_placeholder') }}" 
                                />
                            </div>
                            <div class="pt-7">
                                <button 
                                    type="button" 
                                    @click="autoSlug = !autoSlug" 
                                    class="px-4 py-3 rounded-lg border-2 border-gray-200 hover:border-blue-500 transition-colors"
                                    :class="autoSlug ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white text-gray-600'"
                                >
                                    <span x-show="autoSlug">{{ __('app.common.auto') }}</span>
                                    <span x-show="!autoSlug">{{ __('app.common.manual') }}</span>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">{{ __('app.admin.code_summary.slug_help') }}</p>

                        {{-- Summary Field with Bilingual Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    {{ __('app.admin.categories.summary') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <select x-model="summaryLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="en">🇬🇧 English</option>
                                        <option value="ja">🇯🇵 日本語</option>
                                    </select>
                                    <span x-show="translatingSummary" class="text-xs text-gray-500 flex items-center gap-1" x-cloak>
                                        <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('app.common.translating') }}
                                    </span>
                                </div>
                            </div>
                            <div x-show="summaryLang === 'en'" 
                                 x-transition:enter="transition ease-out duration-200" 
                                 x-transition:enter-start="opacity-0" 
                                 x-transition:enter-end="opacity-100"
                                 x-cloak>
                                <textarea 
                                    name="summary[en]" 
                                    x-model="summaryEn"
                                    @input="handleSummaryInput($event.target.value, 'en')"
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none resize-none" 
                                    placeholder="{{ __('app.admin.categories.summary_placeholder') }}"
                                    required></textarea>
                                <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_english_auto_translate') }}</p>
                            </div>
                            <div x-show="summaryLang === 'ja'" 
                                 x-transition:enter="transition ease-out duration-200" 
                                 x-transition:enter-start="opacity-0" 
                                 x-transition:enter-end="opacity-100"
                                 x-cloak>
                                <textarea 
                                    name="summary[ja]" 
                                    x-model="summaryJa"
                                    @input="handleSummaryInput($event.target.value, 'ja')"
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none resize-none" 
                                    placeholder="{{ __('app.admin.categories.summary_placeholder') }}"
                                    required></textarea>
                                <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.code_summary.language') }}
                                </label>
                                <input 
                                    name="language" 
                                    @input="previewData.language = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.code_summary.language_placeholder') }}" 
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.code_summary.framework') }}
                                </label>
                                <input 
                                    name="framework" 
                                    @input="previewData.framework = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.code_summary.framework_placeholder') }}" 
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.code') }}
                            </label>
                            <textarea 
                                name="code" 
                                @input="previewData.code = $event.target.value"
                                rows="15" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none resize-none font-mono text-sm" 
                                placeholder="{{ __('app.admin.code_summary.code_placeholder') }}"
                            ></textarea>
                            <p class="text-xs text-gray-500 mt-2">{{ __('app.admin.code_summary.code_empty_warning') }}</p>
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
                            {{ __('app.admin.code_summary.context_purpose') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.problem_statement') }}
                            </label>
                            <input 
                                name="problem_statement" 
                                @input="previewData.problem_statement = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.code_summary.problem_statement_placeholder') }}" 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.learning_goal') }}
                            </label>
                            <textarea 
                                name="learning_goal" 
                                @input="previewData.learning_goal = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.code_summary.learning_goal_placeholder') }}"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.use_case') }}
                            </label>
                            <textarea 
                                name="use_case" 
                                @input="previewData.use_case = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.code_summary.use_case_placeholder') }}"
                            ></textarea>
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
                            {{ __('app.admin.code_summary.proof_reproducibility') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.how_to_run') }} <span class="text-red-500">*</span> ({{ __('app.admin.code_summary.if_code_empty') }})
                            </label>
                            <textarea 
                                name="how_to_run" 
                                @input="previewData.how_to_run = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none font-mono text-sm" 
                                placeholder="{{ __('app.admin.code_summary.how_to_run_placeholder') }}&#10;{{ __('app.admin.code_summary.how_to_run_example') }}"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.expected_output') }} <span class="text-red-500">*</span> ({{ __('app.admin.code_summary.if_code_empty') }})
                            </label>
                            <textarea 
                                name="expected_output" 
                                @input="previewData.expected_output = $event.target.value"
                                rows="4" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.code_summary.expected_output_placeholder') }}"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.dependencies') }}
                            </label>
                            <input 
                                name="dependencies" 
                                @input="previewData.dependencies = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.code_summary.dependencies_placeholder') }}" 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.test_status') }}
                            </label>
                            <input 
                                name="test_status" 
                                @input="previewData.test_status = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.code_summary.test_status_placeholder') }}" 
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
                            {{ __('app.admin.code_summary.evaluation_reflection') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.complexity_notes') }}
                            </label>
                            <textarea 
                                name="complexity_notes" 
                                @input="previewData.complexity_notes = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.code_summary.complexity_notes_placeholder') }}"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.security_notes') }}
                            </label>
                            <textarea 
                                name="security_notes" 
                                @input="previewData.security_notes = $event.target.value"
                                rows="3" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.code_summary.security_notes_placeholder') }}"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.reflection') }}
                            </label>
                            <textarea 
                                name="reflection" 
                                @input="previewData.reflection = $event.target.value"
                                rows="4" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="{{ __('app.admin.code_summary.reflection_placeholder') }}"
                            ></textarea>
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
                            {{ __('app.admin.code_summary.traceability') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.code_summary.commit_sha') }}
                                </label>
                                <input 
                                    name="commit_sha" 
                                    @input="previewData.commit_sha = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none font-mono text-sm" 
                                    placeholder="{{ __('app.admin.code_summary.commit_sha_placeholder') }}" 
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.code_summary.license') }}
                                </label>
                                <input 
                                    name="license" 
                                    @input="previewData.license = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.code_summary.license_placeholder') }}" 
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.file_path_repo') }}
                            </label>
                            <input 
                                name="file_path_repo" 
                                @input="previewData.file_path_repo = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none font-mono text-sm" 
                                placeholder="{{ __('app.admin.code_summary.file_path_repo_placeholder') }}" 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.repository_url') }}
                            </label>
                            <input 
                                name="repository_url" 
                                type="url"
                                @input="previewData.repository_url = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.code_summary.repository_url_placeholder') }}" 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.file_path') }}
                            </label>
                            <input 
                                name="file_path" 
                                @input="previewData.file_path = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none font-mono text-sm" 
                                placeholder="{{ __('app.admin.code_summary.file_path_placeholder') }}" 
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
                            {{ __('app.admin.code_summary.metadata') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.code_summary.difficulty') }}
                                </label>
                                <select 
                                    name="difficulty" 
                                    @change="previewData.difficulty = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none"
                                >
                                    <option value="">{{ __('app.admin.code_summary.difficulty_select') }}</option>
                                    <option value="Beginner">{{ __('app.common.beginner') }}</option>
                                    <option value="Intermediate">{{ __('app.common.intermediate') }}</option>
                                    <option value="Advanced">{{ __('app.common.advanced') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.code_summary.time_spent') }}
                                </label>
                                <input 
                                    name="time_spent" 
                                    type="number"
                                    min="0"
                                    @input="previewData.time_spent = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.code_summary.time_spent_placeholder') }}" 
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('app.admin.code_summary.status') }}
                                </label>
                                <select 
                                    name="status" 
                                    @change="previewData.status = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none"
                                >
                                    <option value="completed">{{ __('app.common.completed') }}</option>
                                    <option value="in_progress">{{ __('app.common.in_progress') }}</option>
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
                            {{ __('app.admin.code_summary.organization') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.categories') }}
                            </label>
                            <select 
                                name="categories[]" 
                                multiple 
                                @change="handleCategoryChange($event)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none min-h-[150px]"
                            >
                                @forelse($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->getTranslated('name') ?: $category->slug }}</option>
                                @empty
                                    <option value="" disabled>No categories available. Please create categories first.</option>
                                @endforelse
                            </select>
                            <p class="text-xs text-gray-500 mt-2">{{ __('app.admin.code_summary.categories_hint') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.sections') }}
                            </label>
                            <select 
                                name="sections[]" 
                                multiple 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none min-h-[150px]"
                            >
                                @forelse($sections ?? [] as $section)
                                    <option value="{{ $section->id }}">{{ $section->category->getTranslated('name') ?: $section->category->slug }} → {{ $section->getTranslated('title') ?: $section->slug }}</option>
                                @empty
                                    <option value="" disabled>No sections available. Please create sections first.</option>
                                @endforelse
                            </select>
                            <p class="text-xs text-gray-500 mt-2">{{ __('app.admin.code_summary.sections_hint') }}</p>
                            
                            <div class="mt-4">
                                <button 
                                    type="button" 
                                    @click="showNewSectionForm = !showNewSectionForm" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-teal-600 hover:text-teal-700 hover:bg-teal-50 rounded-lg transition-colors border border-teal-200"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('app.admin.code_summary.add_new_section') }}
                                </button>
                            </div>
                            
                            <div x-show="showNewSectionForm" x-transition class="mt-4 p-6 bg-gradient-to-br from-teal-50 to-cyan-50 rounded-xl border-2 border-teal-200">
                                <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ __('app.admin.code_summary.create_new_section') }}
                                </h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.admin.code_summary.category') }}</label>
                                        <select 
                                            name="new_section_category_id"
                                            x-model="newSectionCategoryId" 
                                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                            required
                                        >
                                            <option value="">{{ __('app.admin.code_summary.select_category') }}</option>
                                            @forelse($categories ?? [] as $category)
                                                <option value="{{ $category->id }}">{{ $category->getTranslated('name') ?: $category->slug }}</option>
                                            @empty
                                                <option value="" disabled>No categories available</option>
                                            @endforelse
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.admin.code_summary.section_name') }}</label>
                                        <input 
                                            type="text" 
                                            x-model="newSectionName" 
                                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                            placeholder="{{ __('app.admin.code_summary.section_name_placeholder') }}" 
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('app.admin.code_summary.description_optional') }}</label>
                                        <textarea 
                                            x-model="newSectionDescription" 
                                            rows="3" 
                                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none resize-none" 
                                            placeholder="{{ __('app.admin.code_summary.description_placeholder') }}"
                                        ></textarea>
                                    </div>
                                    <div class="flex gap-3">
                                        <button 
                                            type="button" 
                                            @click="createNewSection()" 
                                            :disabled="loading" 
                                            class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-lg font-medium disabled:opacity-50 transition-all shadow-md hover:shadow-lg"
                                        >
                                            <span x-show="!loading">{{ __('app.admin.code_summary.create_and_add') }}</span>
                                            <span x-show="loading" class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                {{ __('app.admin.code_summary.creating') }}
                                            </span>
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="showNewSectionForm = false; newSectionName = ''; newSectionDescription = ''; newSectionCategoryId = '';" 
                                            class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-all"
                                        >
                                            {{ __('app.common.cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('app.admin.code_summary.tags') }}
                            </label>
                            <input 
                                name="tags" 
                                @input="previewData.tags = normalizeTags($event.target.value)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                placeholder="{{ __('app.admin.code_summary.tags_placeholder') }}" 
                            />
                            <p class="text-xs text-gray-500 mt-2">{{ __('app.admin.code_summary.tags_hint') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a 
                        href="{{ route('admin.code-summaries.index') }}" 
                        class="px-6 py-3 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        {{ __('app.common.cancel') }}
                    </a>
                    <button 
                        type="submit" 
                        class="px-8 py-3 text-sm font-semibold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg transition-all shadow-lg hover:shadow-xl"
                    >
                        {{ __('app.admin.code_summary.create') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Preview Column (1/3 width) --}}
        <div class="lg:col-span-1">
            <div class="sticky top-8">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{ __('app.admin.code_summary.live_preview') }}
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- Badges Row --}}
                        <div class="flex flex-wrap gap-2" x-show="previewData.status || previewData.difficulty || previewData.time_spent">
                            <template x-if="previewData.status === 'completed'">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    ✓ {{ __('app.common.completed') }}
                                </span>
                            </template>
                            <template x-if="previewData.status === 'in_progress'">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    ⏳ {{ __('app.common.in_progress') }}
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
                            <template x-if="previewData.time_spent">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    ⏱ <span x-text="previewData.time_spent"></span> <span x-text="window.codeSummaryCreateData.translations.min"></span>
                                </span>
                            </template>
                        </div>

                        {{-- Title --}}
                        <div x-show="previewData.title">
                            <h3 class="text-lg font-bold text-gray-900 mb-2" x-text="previewData.title || window.codeSummaryCreateData.translations.code_summary_title"></h3>
                        </div>

                        {{-- Summary --}}
                        <div x-show="previewData.summary">
                            <p class="text-sm text-gray-600 leading-relaxed mb-3" x-text="previewData.summary"></p>
                        </div>

                        {{-- Language & Framework --}}
                        <div class="flex flex-wrap gap-2" x-show="previewData.language || previewData.framework">
                            <template x-if="previewData.language">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    <span x-text="previewData.language"></span>
                                </span>
                            </template>
                            <template x-if="previewData.framework">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                    <span x-text="previewData.framework"></span>
                                </span>
                            </template>
                        </div>

                        {{-- Problem Statement --}}
                        <div x-show="previewData.problem_statement" class="p-3 bg-green-50 rounded-lg border-l-4 border-green-500">
                            <p class="text-xs font-semibold text-green-800 uppercase tracking-wide mb-1">{{ __('app.common.problem') }}</p>
                            <p class="text-sm text-gray-700" x-text="previewData.problem_statement"></p>
                        </div>

                        {{-- Code Preview --}}
                        <div x-show="previewData.code" class="bg-gray-900 rounded-lg overflow-hidden border-2 border-gray-800">
                            <div class="px-3 py-2 bg-gray-800 border-b border-gray-700 flex items-center gap-2">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <span class="text-xs text-gray-400 ml-2" x-text="previewData.language || window.codeSummaryCreateData.translations.code"></span>
                            </div>
                            <pre class="p-3 overflow-x-auto max-h-40"><code class="text-gray-100 font-mono text-xs" x-text="previewData.code.substring(0, 200) + (previewData.code.length > 200 ? '...' : '')"></code></pre>
                        </div>

                        {{-- Tags --}}
                        <div x-show="previewData.tags" class="flex flex-wrap gap-2">
                            <template x-for="tag in (previewData.tags.split(',').filter(t => t.trim()))" :key="tag">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-300" x-text="tag.trim()"></span>
                            </template>
                        </div>

                        {{-- Empty State --}}
                        <div x-show="!previewData.title && !previewData.summary" class="text-center py-8 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            <p class="text-sm">{{ __('app.admin.code_summary.preview_will_appear') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
