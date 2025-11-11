@extends('layouts.app')
@section('title', 'Create New Book Page')
@section('content')
    <script>
        window.bookPageCreateData = {
            sections: @js($sections->map(function($s) {
                return ['id' => $s->id, 'name' => $s->getTranslated('title'), 'title' => $s->getTranslated('title'), 'category_id' => $s->category_id, 'category_name' => $s->category->getTranslated('name')];
            })),
            selectedCategories: []
        };
        
        // Register Alpine.js component data - works for both normal page load and modal load
        window.registerBookPageCreateComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                // Always register/overwrite the component
                try {
                    window.Alpine.data('bookPageCreate', () => ({
                sections: window.bookPageCreateData?.sections || [],
                selectedCategories: window.bookPageCreateData?.selectedCategories || [],
                showNewSectionForm: false,
                newSectionName: '',
                newSectionCategoryId: '',
                newSectionDescription: '',
                loading: false,
                autoSlug: true,
                previewData: {
                    title: '',
                    summary: '',
                    book_title: '',
                    author: '',
                    page_number: '',
                    read_at: '',
                    key_objectives: '',
                    reflection: '',
                    applied_snippet: '',
                    references: '',
                    how_to_run: '',
                    result_evidence: '',
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
            window.registerBookPageCreateComponent();
        } else {
            // Otherwise wait for Alpine to initialize (for normal page loads)
            document.addEventListener('alpine:init', window.registerBookPageCreateComponent);
        }
        
        // Alpine.js component for book page form with auto-translation
        // Register globally so it's available for modal loads
        window.registerBookPageTranslationComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                try {
                    window.Alpine.data('bookPageTranslationData', () => ({
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
                                                if (window.Alpine && document.querySelector('[x-data*="bookPageCreate"]')) {
                                                    const bookPageCreate = Alpine.$data(document.querySelector('[x-data*="bookPageCreate"]'));
                                                    if (bookPageCreate && bookPageCreate.previewData) {
                                                        bookPageCreate.previewData.title = data.translated;
                                                    }
                                                }
                                            } else {
                                                this.titleJa = data.translated;
                                            }
                                        } else {
                                            if (toLang === 'en') {
                                                this.summaryEn = data.translated;
                                                // Update preview
                                                if (window.Alpine && document.querySelector('[x-data*="bookPageCreate"]')) {
                                                    const bookPageCreate = Alpine.$data(document.querySelector('[x-data*="bookPageCreate"]'));
                                                    if (bookPageCreate && bookPageCreate.previewData) {
                                                        bookPageCreate.previewData.summary = data.translated;
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
                                if (window.Alpine && document.querySelector('[x-data*="bookPageCreate"]')) {
                                    const bookPageCreate = Alpine.$data(document.querySelector('[x-data*="bookPageCreate"]'));
                                    if (bookPageCreate && bookPageCreate.previewData) {
                                        bookPageCreate.previewData.title = value;
                                    }
                                    if (bookPageCreate && bookPageCreate.generateSlug) {
                                        bookPageCreate.generateSlug(value);
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
                                if (window.Alpine && document.querySelector('[x-data*="bookPageCreate"]')) {
                                    const bookPageCreate = Alpine.$data(document.querySelector('[x-data*="bookPageCreate"]'));
                                    if (bookPageCreate && bookPageCreate.previewData) {
                                        bookPageCreate.previewData.summary = value;
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
                    console.error('Error registering bookPageTranslationData component:', e);
                }
            }
        };
        
        // Register translation component immediately if Alpine is already loaded
        if (window.Alpine && window.Alpine.data) {
            window.registerBookPageTranslationComponent();
        } else {
            document.addEventListener('alpine:init', window.registerBookPageTranslationComponent);
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>

    {{-- Hero Header --}}
    <div class="mb-4 sm:mb-8">
        <div class="bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8 text-white">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-1 sm:mb-2">Create New Book Page</h1>
                    <p class="text-purple-100 text-sm sm:text-base lg:text-lg">Document your reading journey with comprehensive details</p>
                </div>
                <div class="hidden md:block flex-shrink-0 ml-4">
                    <div class="w-16 h-16 lg:w-24 lg:h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-8 h-8 lg:w-12 lg:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Two Column Layout: Form Left, Preview Right --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8" x-data="bookPageCreate()">
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

            <form method="POST" action="{{ route('admin.book-pages.store') }}" class="space-y-6">
                @csrf

                {{-- Basic Information Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Basic Information
                        </h2>
                    </div>
                    <div class="p-6 space-y-6" x-data="bookPageTranslationData()">
                        {{-- Title Field with Bilingual Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    {{ __('app.admin.book_page.title') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <select x-model="titleLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        <option value="en">🇬🇧 English</option>
                                        <option value="ja">🇯🇵 日本語</option>
                                    </select>
                                    <span x-show="translatingTitle" class="text-xs text-gray-500 flex items-center gap-1">
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
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.book_page.title_placeholder') }}"
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
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                    placeholder="{{ __('app.admin.book_page.title_placeholder') }}"
                                    required />
                                <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Slug
                            </label>
                            <div class="flex items-center gap-2">
                                <input 
                                    name="slug" 
                                    class="flex-1 px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                    placeholder="Auto-generated if empty" />
                                <button 
                                    type="button"
                                    @click="autoSlug = !autoSlug"
                                    class="px-4 py-3 rounded-lg border-2 border-gray-200 hover:border-purple-500 transition-colors"
                                    :class="autoSlug ? 'bg-purple-50 border-purple-500 text-purple-700' : 'bg-white text-gray-600'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Auto-generated from title if left empty</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Content (What you read)
                            </label>
                            <textarea 
                                name="content" 
                                rows="8" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                placeholder="Enter the content you read from the book page..."></textarea>
                        </div>

                        {{-- Summary Field with Bilingual Input --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-700">
                                    {{ __('app.admin.categories.summary') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <select x-model="summaryLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
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
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
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
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="{{ __('app.admin.categories.summary_placeholder') }}"
                                    required></textarea>
                                <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Required field</p>
                        </div>
                    </div>
                </div>

                {{-- Learning Outcomes & Proof Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Learning Outcomes & Proof
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Key Objectives (one per line)
                            </label>
                            <textarea 
                                name="key_objectives" 
                                @input="previewData.key_objectives = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none font-mono text-sm" 
                                placeholder="• Objective 1&#10;• Objective 2&#10;• Objective 3"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Enter one objective per line</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Reflection / Insight (2-4 sentences)
                            </label>
                            <textarea 
                                name="reflection" 
                                @input="previewData.reflection = $event.target.value"
                                rows="4" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none" 
                                placeholder="What clicked? What was hard? What will you apply?"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Applied Snippet / Exercise (code or screenshot link)
                            </label>
                            <textarea 
                                name="applied_snippet" 
                                @input="previewData.applied_snippet = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none resize-none font-mono text-sm" 
                                placeholder="Paste code snippet or link to screenshot/exercise"></textarea>
                            <p class="text-xs text-red-500 mt-1">⚠️ If empty, Result/Evidence becomes required</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                References / ISBN / Page Range
                            </label>
                            <input 
                                name="references" 
                                @input="previewData.references = $event.target.value"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none" 
                                placeholder="e.g., ISBN: 978-0132350884, Pages 42-58" />
                        </div>
                    </div>
                </div>

                {{-- Reproducibility Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            Reproducibility
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                How to Run / Recreate (exact commands or steps)
                            </label>
                            <textarea 
                                name="how_to_run" 
                                @input="previewData.how_to_run = $event.target.value"
                                rows="6" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none font-mono text-sm" 
                                placeholder="Step 1: Command here&#10;Step 2: Command here"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Result / Evidence (expected output, screenshot, or link to gist)
                            </label>
                            <textarea 
                                name="result_evidence" 
                                @input="previewData.result_evidence = $event.target.value"
                                rows="4" 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none" 
                                placeholder="Expected output or link to screenshot/gist"></textarea>
                            <p class="text-xs text-red-500 mt-1">⚠️ Required if Applied Snippet is empty</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Difficulty
                                </label>
                                <select 
                                    name="difficulty" 
                                    @change="previewData.difficulty = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none">
                                    <option value="">Select difficulty...</option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Time Spent (minutes)
                                </label>
                                <input 
                                    type="number" 
                                    name="time_spent" 
                                    @input="previewData.time_spent = $event.target.value"
                                    min="0"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                    placeholder="e.g., 45" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Book Details Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Book Details
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Book Title
                                </label>
                                <input 
                                    name="book_title" 
                                    @input="previewData.book_title = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="e.g., Clean Code" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Author
                                </label>
                                <input 
                                    name="author" 
                                    @input="previewData.author = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="e.g., Robert C. Martin" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Page Number
                                </label>
                                <input 
                                    type="number" 
                                    name="page_number" 
                                    @input="previewData.page_number = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                    placeholder="e.g., 42" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Read At
                                </label>
                                <input 
                                    type="date" 
                                    name="read_at" 
                                    @change="previewData.read_at = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" />
                                <p class="text-xs text-gray-500 mt-1">Format: YYYY-MM-DD</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Status
                                </label>
                                <select 
                                    name="status" 
                                    @change="previewData.status = $event.target.value"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                    <option value="completed">Completed</option>
                                    <option value="in_progress">In Progress</option>
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
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none min-h-[150px]">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->getTranslated('name') ?: $category->slug }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Hold Ctrl/Cmd to select multiple categories
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Sections (grouping within categories)
                            </label>
                            <select 
                                name="sections[]" 
                                multiple 
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none min-h-[150px]">
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">
                                        {{ $section->category->getTranslated('name') ?: $section->category->slug }} → {{ $section->getTranslated('title') ?: $section->slug }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1 mb-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Hold Ctrl/Cmd to select multiple sections
                            </p>
                            
                            <button 
                                type="button" 
                                @click="showNewSectionForm = !showNewSectionForm" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 hover:from-teal-600 hover:to-cyan-600 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg transform hover:scale-105">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add New Section
                            </button>
                        </div>

                        {{-- New Section Form --}}
                        <div 
                            x-show="showNewSectionForm" 
                            x-cloak 
                            x-transition
                            class="mt-4 p-6 bg-gradient-to-br from-teal-50 to-cyan-50 rounded-xl border-2 border-teal-200">
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
                                        required>
                                        <option value="">Select a category...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->getTranslated('name') ?: $category->slug }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Section Name</label>
                                    <input 
                                        type="text" 
                                        x-model="newSectionName" 
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" 
                                        placeholder="e.g., Lesson 1" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description (optional)</label>
                                    <textarea 
                                        x-model="newSectionDescription" 
                                        rows="3" 
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none resize-none" 
                                        placeholder="Brief description..."></textarea>
                                </div>
                                <div class="flex gap-3">
                                    <button 
                                        type="button" 
                                        @click="createNewSection()" 
                                        :disabled="loading" 
                                        class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-lg font-medium disabled:opacity-50 transition-all shadow-md hover:shadow-lg">
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
                                        class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-all">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tags Section --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Tags
                        </h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tags (comma separated, max 5)
                            </label>
                                <input 
                                name="tags" 
                                @input="previewData.tags = normalizeTags($event.target.value)"
                                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                placeholder="Laravel, PHP, Security, Best Practices" />
                            <p class="text-xs text-gray-500 mt-2">Separate tags with commas. Maximum 5 tags. Tags are automatically lowercased.</p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between pt-6 pb-8">
                    <a 
                        href="{{ route('admin.book-pages.index') }}" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 hover:from-purple-700 hover:via-indigo-700 hover:to-blue-700 text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Create Book Page
                    </button>
                </div>
            </form>
        </div>

        {{-- Preview Column (1/3 width) --}}
        <div class="lg:col-span-1">
            <div class="sticky top-8">
                <div class="bg-white rounded-xl shadow-xl border-2 border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Live Preview
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- Badges --}}
                        <div class="flex flex-wrap gap-2" x-show="previewData.status || previewData.difficulty || previewData.time_spent">
                            <template x-if="previewData.status === 'completed'">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    ✓ Completed
                                </span>
                            </template>
                            <template x-if="previewData.status === 'in_progress'">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    ⏳ In Progress
                                </span>
                            </template>
                            <template x-if="previewData.difficulty">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-blue-100 text-blue-800': previewData.difficulty === 'Beginner',
                                        'bg-orange-100 text-orange-800': previewData.difficulty === 'Intermediate',
                                        'bg-red-100 text-red-800': previewData.difficulty === 'Advanced'
                                    }"
                                    x-text="previewData.difficulty">
                                </span>
                            </template>
                            <template x-if="previewData.time_spent">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    ⏱ <span x-text="previewData.time_spent"></span> min
                                </span>
                            </template>
                        </div>

                        {{-- Title --}}
                        <div x-show="previewData.title">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2" x-text="previewData.title || 'Untitled'"></h3>
                        </div>

                        {{-- Book Info --}}
                        <div class="text-sm text-gray-600 space-y-1" x-show="previewData.book_title || previewData.author || previewData.page_number">
                            <template x-if="previewData.book_title">
                                <p><span class="font-semibold">Book:</span> <span x-text="previewData.book_title"></span></p>
                            </template>
                            <template x-if="previewData.author">
                                <p><span class="font-semibold">Author:</span> <span x-text="previewData.author"></span></p>
                            </template>
                            <template x-if="previewData.page_number">
                                <p><span class="font-semibold">Page:</span> <span x-text="previewData.page_number"></span></p>
                            </template>
                            <template x-if="previewData.read_at">
                                <p><span class="font-semibold">Read:</span> <span x-text="previewData.read_at"></span></p>
                            </template>
                        </div>

                        {{-- Summary --}}
                        <div x-show="previewData.summary">
                            <p class="text-gray-700 text-sm leading-relaxed" x-text="previewData.summary"></p>
                        </div>

                        {{-- Key Objectives --}}
                        <div x-show="previewData.key_objectives" class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-semibold text-gray-900 mb-2">Key Objectives:</h4>
                            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                                <template x-for="(obj, index) in (previewData.key_objectives ? previewData.key_objectives.split('\n').filter(o => o.trim()) : [])" :key="index">
                                    <li x-text="obj.trim()"></li>
                                </template>
                            </ul>
                        </div>

                        {{-- Reflection --}}
                        <div x-show="previewData.reflection">
                            <h4 class="font-semibold text-gray-900 mb-2">Reflection:</h4>
                            <p class="text-sm text-gray-700 leading-relaxed" x-text="previewData.reflection"></p>
                        </div>

                        {{-- Tags --}}
                        <div x-show="previewData.tags" class="flex flex-wrap gap-2">
                            <template x-for="(tag, index) in (previewData.tags ? previewData.tags.split(',').map(t => t.trim()).filter(t => t).slice(0, 5) : [])" :key="index">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-700" x-text="tag"></span>
                            </template>
                        </div>

                        {{-- Empty State --}}
                        <div x-show="!previewData.title && !previewData.summary" class="text-center py-8 text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <p class="text-sm">Start filling the form to see preview</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
