@extends('layouts.app')
@section('title', __('app.admin.hero_section'))
@section('content')
<div class="max-w-6xl mx-auto p-6" x-data="{ showResetModal: false }">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('app.admin.hero.edit_hero_section') }}</h1>
        <p class="text-gray-600 mt-1">{{ __('app.admin.hero.customize_description') }}</p>
    </div>

    {{-- Success Message --}}
    @if(session('status'))
    <div x-data="{ show: true }" 
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg flex items-center justify-between animate-slide-down">
        <div class="flex items-center gap-3 flex-1">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-green-800 font-medium">{{ session('status') }}</p>
        </div>
        <button @click="show = false" 
                class="flex-shrink-0 text-green-600 hover:text-green-800 ml-4 transition-colors"
                aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.hero.update') }}" enctype="multipart/form-data" class="space-y-8" id="heroForm">
        @csrf
        @method('PUT')

        {{-- Background Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.hero.background') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="background_color" value="{{ old('background_color', $heroSection->background_color ?? '#e0e7ff') }}" class="h-10 w-20 rounded border border-gray-300">
                        <input type="text" value="{{ old('background_color', $heroSection->background_color ?? '#e0e7ff') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2" placeholder="#e0e7ff">
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Badge/Tagline Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.hero.badge_tagline') }}</h2>
            <div class="space-y-4">
                <div>
                    <x-dual-language-input 
                        name="badge_text" 
                        label="{{ __('app.admin.hero.badge_text') }}" 
                        :value="$heroSection->getTranslations('badge_text')"
                        placeholder="IT / UIUX / Security"
                        required
                    />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.badge_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="badge_color" value="{{ old('badge_color', $heroSection->badge_color ?? '#ffb400') }}" class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" value="{{ old('badge_color', $heroSection->badge_color ?? '#ffb400') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2" placeholder="#ffb400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.text_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="badge_text_color" value="{{ old('badge_text_color', $heroSection->badge_text_color ?? '#000000') }}" class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" value="{{ old('badge_text_color', $heroSection->badge_text_color ?? '#000000') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2" placeholder="#000000">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.mobile_size') }}</label>
                        <select name="badge_size_mobile" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            <option value="text-[10px]" {{ ($heroSection->badge_size_mobile ?? 'text-xs') == 'text-[10px]' ? 'selected' : '' }}>Text 10px</option>
                            <option value="text-xs" {{ ($heroSection->badge_size_mobile ?? 'text-xs') == 'text-xs' ? 'selected' : '' }}>{{ __('app.admin.hero.text_3xl') }}</option>
                            <option value="text-sm" {{ ($heroSection->badge_size_mobile ?? 'text-xs') == 'text-sm' ? 'selected' : '' }}>{{ __('app.admin.hero.text_4xl') }}</option>
                            <option value="text-base" {{ ($heroSection->badge_size_mobile ?? 'text-xs') == 'text-base' ? 'selected' : '' }}>Text Base</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.tablet_size') }}</label>
                        <select name="badge_size_tablet" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            <option value="text-xs" {{ ($heroSection->badge_size_tablet ?? 'text-sm') == 'text-xs' ? 'selected' : '' }}>{{ __('app.admin.hero.text_3xl') }}</option>
                            <option value="text-sm" {{ ($heroSection->badge_size_tablet ?? 'text-sm') == 'text-sm' ? 'selected' : '' }}>{{ __('app.admin.hero.text_4xl') }}</option>
                            <option value="text-base" {{ ($heroSection->badge_size_tablet ?? 'text-sm') == 'text-base' ? 'selected' : '' }}>Text Base</option>
                            <option value="text-lg" {{ ($heroSection->badge_size_tablet ?? 'text-sm') == 'text-lg' ? 'selected' : '' }}>Text LG</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.desktop_size') }}</label>
                        <select name="badge_size_desktop" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            <option value="text-sm" {{ ($heroSection->badge_size_desktop ?? 'text-sm') == 'text-sm' ? 'selected' : '' }}>{{ __('app.admin.hero.text_4xl') }}</option>
                            <option value="text-base" {{ ($heroSection->badge_size_desktop ?? 'text-sm') == 'text-base' ? 'selected' : '' }}>Text Base</option>
                            <option value="text-lg" {{ ($heroSection->badge_size_desktop ?? 'text-sm') == 'text-lg' ? 'selected' : '' }}>Text LG</option>
                            <option value="text-xl" {{ ($heroSection->badge_size_desktop ?? 'text-sm') == 'text-xl' ? 'selected' : '' }}>Text XL</option>
                        </select>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Heading Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.hero.main_heading') }}</h2>
            <div class="space-y-4">
                <div>
                    <x-dual-language-input 
                        name="heading_text" 
                        label="{{ __('app.admin.hero.heading_text') }}" 
                        :value="$heroSection->getTranslations('heading_text')"
                        placeholder="1️⃣ Typing Animation (Developer-style intro)"
                        :rows="3"
                    />
                    <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.hero.heading_typing_hint') }}</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.mobile_size') }}</label>
                        <select name="heading_size_mobile" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            <option value="text-3xl" {{ ($heroSection->heading_size_mobile ?? 'text-4xl') == 'text-3xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_3xl') }}</option>
                            <option value="text-4xl" {{ ($heroSection->heading_size_mobile ?? 'text-4xl') == 'text-4xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_4xl') }}</option>
                            <option value="text-5xl" {{ ($heroSection->heading_size_mobile ?? 'text-4xl') == 'text-5xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_5xl') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.tablet_size') }}</label>
                        <select name="heading_size_tablet" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            <option value="text-4xl" {{ ($heroSection->heading_size_tablet ?? 'text-5xl') == 'text-4xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_4xl') }}</option>
                            <option value="text-5xl" {{ ($heroSection->heading_size_tablet ?? 'text-5xl') == 'text-5xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_5xl') }}</option>
                            <option value="text-6xl" {{ ($heroSection->heading_size_tablet ?? 'text-5xl') == 'text-6xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_6xl') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.desktop_size') }}</label>
                        <select name="heading_size_desktop" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            <option value="text-5xl" {{ ($heroSection->heading_size_desktop ?? 'text-6xl') == 'text-5xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_5xl') }}</option>
                            <option value="text-6xl" {{ ($heroSection->heading_size_desktop ?? 'text-6xl') == 'text-6xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_6xl') }}</option>
                            <option value="text-7xl" {{ ($heroSection->heading_size_desktop ?? 'text-6xl') == 'text-7xl' ? 'selected' : '' }}>{{ __('app.admin.hero.text_7xl') }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.text_color') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="heading_text_color" value="{{ old('heading_text_color', $heroSection->heading_text_color ?? '#111827') }}" class="h-10 w-20 rounded border border-gray-300">
                        <input type="text" value="{{ old('heading_text_color', $heroSection->heading_text_color ?? '#111827') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2" placeholder="#111827">
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Subheading Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.hero.subheading') }}</h2>
            <div class="space-y-4">
                <div>
                    <x-dual-language-input 
                        name="subheading_text" 
                        label="{{ __('app.admin.hero.subheading_text') }}" 
                        :value="$heroSection->getTranslations('subheading_text')"
                        placeholder="Each letter &quot;types in,&quot; like a command line. Feels personal and smart."
                        :rows="2"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.text_color') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="subheading_text_color" value="{{ old('subheading_text_color', $heroSection->subheading_text_color ?? '#6b7280') }}" class="h-10 w-20 rounded border border-gray-300">
                        <input type="text" value="{{ old('subheading_text_color', $heroSection->subheading_text_color ?? '#6b7280') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2" placeholder="#6b7280">
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Buttons Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.hero.buttons') }}</h2>
            
            {{-- Button 1 --}}
            <div class="border-b border-gray-200 pb-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.admin.hero.button_1_primary') }}</h3>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="button1_visible" value="1" {{ ($heroSection->button1_visible ?? true) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm text-gray-700">{{ __('app.admin.hero.visible') }}</span>
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-dual-language-input 
                            name="button1_text" 
                            label="{{ __('app.admin.hero.button_text') }}" 
                            :value="$heroSection->getTranslations('button1_text')"
                            placeholder="Projects"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.button_link') }}</label>
                        <input type="text" name="button1_link" value="{{ old('button1_link', $heroSection->button1_link ?? route('projects')) }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="/projects">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.background_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="button1_bg_color" value="{{ old('button1_bg_color', $heroSection->button1_bg_color ?? '#ffb400') }}" class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" value="{{ old('button1_bg_color', $heroSection->button1_bg_color ?? '#ffb400') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.text_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="button1_text_color" value="{{ old('button1_text_color', $heroSection->button1_text_color ?? '#111827') }}" class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" value="{{ old('button1_text_color', $heroSection->button1_text_color ?? '#111827') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Button 2 --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ __('app.admin.hero.button_2_secondary') }}</h3>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="button2_visible" value="1" {{ ($heroSection->button2_visible ?? true) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm text-gray-700">{{ __('app.admin.hero.visible') }}</span>
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-dual-language-input 
                            name="button2_text" 
                            label="{{ __('app.admin.hero.button_text') }}" 
                            :value="$heroSection->getTranslations('button2_text')"
                            placeholder="LinkedIn"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.button_link') }}</label>
                        <input type="text" name="button2_link" value="{{ old('button2_link', $heroSection->button2_link ?? 'https://www.linkedin.com/in/...') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="https://...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.background_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="button2_bg_color" value="{{ old('button2_bg_color', $heroSection->button2_bg_color ?? '#ffffff') }}" class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" value="{{ old('button2_bg_color', $heroSection->button2_bg_color ?? '#ffffff') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.text_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="button2_text_color" value="{{ old('button2_text_color', $heroSection->button2_text_color ?? '#1f2937') }}" class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" value="{{ old('button2_text_color', $heroSection->button2_text_color ?? '#1f2937') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.border_color') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="button2_border_color" value="{{ old('button2_border_color', $heroSection->button2_border_color ?? '#d1d5db') }}" class="h-10 w-20 rounded border border-gray-300">
                            <input type="text" value="{{ old('button2_border_color', $heroSection->button2_border_color ?? '#d1d5db') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Navigation Section --}}
        <x-card class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('app.admin.hero.navigation_links') }}</h2>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="nav_visible" value="1" {{ ($heroSection->nav_visible ?? true) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-medium text-gray-700">{{ __('app.admin.hero.show_navigation') }}</span>
                </label>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.text_color') }}</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="navigation_text_color" value="{{ old('navigation_text_color', $heroSection->navigation_text_color ?? '#374151') }}" class="h-10 w-20 rounded border border-gray-300">
                    <input type="text" value="{{ old('navigation_text_color', $heroSection->navigation_text_color ?? '#374151') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2" placeholder="#374151">
                </div>
            </div>
            
            <div 
                data-links='@json(old('navigation_links', $heroSection->navigation_links ?? []))'
                data-sections='@json($availableSections ?? [])'
                x-data="navigationLinksData()"
            class="space-y-4">
                <template x-for="(link, index) in links" :key="index">
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-4">
                        <input type="hidden" :name="`navigation_links[${index}][id]`" x-model="link.id">
                        <input type="hidden" :name="`navigation_links[${index}][order]`" x-model="link.order">
                        
                        <div class="flex gap-4 items-start">
                            <div class="flex-1">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-sm font-semibold text-gray-700">
                                            {{ __('app.admin.hero.link_text') }}
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <select x-model="link.activeLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="en">🇬🇧 English</option>
                                                <option value="ja">🇯🇵 日本語</option>
                                            </select>
                                            <span x-show="translatingLinks[index]" class="text-xs text-gray-500 flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Translating...
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div x-show="link.activeLang === 'en'" 
                                         x-transition:enter="transition ease-out duration-200" 
                                         x-transition:enter-start="opacity-0" 
                                         x-transition:enter-end="opacity-100"
                                         x-cloak>
                                        <input 
                                            type="text"
                                            :name="`navigation_links[${index}][text][en]`" 
                                            x-model="link.text.en"
                                            @input="handleLinkInput($event.target.value, 'en', index)"
                                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                            placeholder="About" />
                                        <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_english_auto_translate') }}</p>
                                    </div>
                                    
                                    <div x-show="link.activeLang === 'ja'" 
                                         x-transition:enter="transition ease-out duration-200" 
                                         x-transition:enter-start="opacity-0" 
                                         x-transition:enter-end="opacity-100"
                                         x-cloak>
                                        <input 
                                            type="text"
                                            :name="`navigation_links[${index}][text][ja]`" 
                                            x-model="link.text.ja"
                                            @input="handleLinkInput($event.target.value, 'ja', index)"
                                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                            placeholder="About" />
                                        <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.section_id') }}</label>
                                <select :name="`navigation_links[${index}][section_id]`" x-model="link.section_id" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                                    <template x-for="section in availableSections">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <button type="button" @click="removeLink(index)" class="px-4 py-2 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 font-medium whitespace-nowrap">{{ __('app.admin.hero.delete') }}</button>
                            </div>
                        </div>
                    </div>
                </template>
                
                <button type="button" @click="addLink()" class="w-full px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium">
                    + {{ __('app.admin.hero.add_navigation_link') }}
                </button>
            </div>
        </x-card>

        {{-- Layout Controls Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.hero.layout_positioning') }}</h2>
            <div class="space-y-6">
                <div>
                    <label class="flex items-center gap-2 mb-4">
                        <input type="checkbox" name="layout_reversed" value="1" {{ ($heroSection->layout_reversed ?? false) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm font-medium text-gray-700">{{ __('app.admin.hero.reverse_layout') }}</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-4">{{ __('app.admin.hero.reverse_layout_hint') }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.badge_horizontal_offset') }}</label>
                        <div class="flex items-center gap-4">
                            <input type="range" 
                                   name="badge_horizontal_offset" 
                                   min="-100" 
                                   max="100" 
                                   value="{{ old('badge_horizontal_offset', $heroSection->badge_horizontal_offset ?? 0) }}" 
                                   class="flex-1"
                                   oninput="this.nextElementSibling.value = this.value + 'px'">
                            <input type="text" 
                                   value="{{ old('badge_horizontal_offset', $heroSection->badge_horizontal_offset ?? 0) }}px" 
                                   readonly 
                                   class="w-20 text-center rounded-lg border border-gray-300 px-2 py-1 text-sm">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.hero.badge_offset_hint') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.text_horizontal_offset') }}</label>
                        <div class="flex items-center gap-4">
                            <input type="range" 
                                   name="text_horizontal_offset" 
                                   min="-100" 
                                   max="100" 
                                   value="{{ old('text_horizontal_offset', $heroSection->text_horizontal_offset ?? 0) }}" 
                                   class="flex-1"
                                   oninput="this.nextElementSibling.value = this.value + 'px'">
                            <input type="text" 
                                   value="{{ old('text_horizontal_offset', $heroSection->text_horizontal_offset ?? 0) }}px" 
                                   readonly 
                                   class="w-20 text-center rounded-lg border border-gray-300 px-2 py-1 text-sm">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.hero.text_offset_hint') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.image_horizontal_offset') }}</label>
                        <div class="flex items-center gap-4">
                            <input type="range" 
                                   name="image_horizontal_offset" 
                                   min="-100" 
                                   max="100" 
                                   value="{{ old('image_horizontal_offset', $heroSection->image_horizontal_offset ?? 0) }}" 
                                   class="flex-1"
                                   oninput="this.nextElementSibling.value = this.value + 'px'">
                            <input type="text" 
                                   value="{{ old('image_horizontal_offset', $heroSection->image_horizontal_offset ?? 0) }}px" 
                                   readonly 
                                   class="w-20 text-center rounded-lg border border-gray-300 px-2 py-1 text-sm">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.hero.image_offset_hint') }}</p>
                    </div>
                </div>
                
            </div>
        </x-card>

        {{-- Profile Images Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.hero.profile_images') }}</h2>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.image_rotation_interval') }}</label>
                <input type="number" name="image_rotation_interval" value="{{ old('image_rotation_interval', $heroSection->image_rotation_interval ?? 2000) }}" min="500" max="10000" step="500" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.hero.rotation_interval_hint') }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.upload_new_images') }}</label>
                <input type="file" name="profile_images[]" accept="image/*" multiple class="w-full rounded-lg border border-gray-300 px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.hero.select_multiple_images') }}</p>
            </div>
            @if($profileImages->count() > 0)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.current_images') }}</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($profileImages as $image)
                            <div class="relative">
                                <img src="{{ asset('storage/' . $image->path) }}" alt="Profile Image" class="w-full h-32 object-cover rounded-lg border border-gray-300">
                                <label class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center cursor-pointer">
                                    <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="hidden" onchange="this.parentElement.parentElement.style.opacity='0.5'">
                                    <span class="text-xs">×</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-card>

        {{-- Blob/Decorative Element Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.hero.decorative_blob') }}</h2>
            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="blob_visible" value="1" {{ ($heroSection->blob_visible ?? true) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-medium text-gray-700">{{ __('app.admin.hero.show_blob') }}</span>
                </label>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.hero.blob_color') }}</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="blob_color" value="{{ old('blob_color', $heroSection->blob_color ?? '#ffb400') }}" class="h-10 w-20 rounded border border-gray-300">
                    <input type="text" value="{{ old('blob_color', $heroSection->blob_color ?? '#ffb400') }}" oninput="this.previousElementSibling.value=this.value" class="flex-1 rounded-lg border border-gray-300 px-3 py-2">
                </div>
            </div>
        </x-card>

        {{-- Submit Button --}}
        <div class="flex justify-between items-center gap-4">
            <button @click="showResetModal = true" type="button" class="px-6 py-2 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 font-semibold transition-colors">
                {{ __('app.admin.hero.reset_to_defaults') }}
            </button>
            <div class="flex gap-4">
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">{{ __('app.common.cancel') }}</a>
                <button type="submit" form="heroForm" class="px-6 py-2 rounded-lg bg-[#ffb400] text-gray-900 font-semibold hover:bg-[#e6a200] transition-colors">{{ __('app.admin.hero.save_changes') }}</button>
            </div>
        </div>
    </form>

    {{-- Reset Confirmation Modal --}}
    <div x-show="showResetModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showResetModal = false"
         style="display: none;">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="showResetModal = false"></div>
        
        {{-- Modal --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all"
                 @click.stop
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.hero.reset_confirm_title') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('app.admin.hero.reset_confirm_cannot_undo') }}</p>
                        </div>
                    </div>
                </div>
                
                {{-- Modal Body --}}
                <div class="px-6 py-4">
                    <p class="text-gray-700 mb-4">Are you sure you want to reset all hero section settings to factory defaults?</p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-sm font-semibold text-red-800 mb-2">This will:</p>
                        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                            <li>Reset all colors to original values (Background: <span class="font-mono bg-red-100 px-1 rounded">#e0e7ff</span> - Light Blue-Gray)</li>
                            <li>Reset all text fields to defaults</li>
                            <li>Delete <strong>ALL</strong> uploaded profile images permanently</li>
                            <li>Remove custom heading and subheading text</li>
                        </ul>
                    </div>
                    <p class="text-sm text-gray-600">Your current settings will be lost forever.</p>
                </div>
                
                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
                    <button @click="showResetModal = false" 
                            type="button"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium transition-colors">
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('admin.hero.reset') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-medium transition-colors shadow-sm">
                            Yes, Reset to Defaults
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('navigationLinksData', () => ({
        links: [],
        availableSections: [],
        translateTimeouts: {},
        translatingLinks: {},
        init() {
            // Load data from data attributes
            const linksData = JSON.parse(this.$el.getAttribute('data-links') || '[]');
            const sectionsData = JSON.parse(this.$el.getAttribute('data-sections') || '[]');
            this.links = linksData;
            this.availableSections = Array.isArray(sectionsData) ? sectionsData : [];
            
            // Normalize existing links to ensure text is an object and add activeLang
            this.links = this.links.map(link => ({
                ...link,
                text: typeof link.text === 'string' ? { en: link.text, ja: '' } : (link.text || { en: '', ja: '' }),
                activeLang: link.activeLang || '{{ app()->getLocale() }}'
            }));
        },
        async translateText(text, fromLang, toLang, linkIndex) {
            if (!text || text.trim().length === 0) return;
            
            this.translatingLinks[linkIndex] = true;
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
                        if (toLang === 'en') {
                            this.links[linkIndex].text.en = data.translated;
                        } else {
                            this.links[linkIndex].text.ja = data.translated;
                        }
                    }
                }
            } catch (error) {
                console.error('Translation error:', error);
            } finally {
                this.translatingLinks[linkIndex] = false;
            }
        },
        handleLinkInput(value, currentLang, linkIndex) {
            if (currentLang === 'en') {
                this.links[linkIndex].text.en = value;
                clearTimeout(this.translateTimeouts[linkIndex]);
                this.translateTimeouts[linkIndex] = setTimeout(() => {
                    if (value && value.trim().length > 0) {
                        this.translateText(value, 'en', 'ja', linkIndex);
                    }
                }, 1000);
            } else {
                this.links[linkIndex].text.ja = value;
                clearTimeout(this.translateTimeouts[linkIndex]);
                this.translateTimeouts[linkIndex] = setTimeout(() => {
                    if (value && value.trim().length > 0) {
                        this.translateText(value, 'ja', 'en', linkIndex);
                    }
                }, 1000);
            }
        },
        addLink() {
            const maxId = this.links.length > 0 ? Math.max(...this.links.map(l => l.id || 0)) : 0;
            this.links.push({
                id: maxId + 1,
                text: { en: 'New Link', ja: '' },
                section_id: 'discover',
                order: this.links.length + 1,
                activeLang: '{{ app()->getLocale() }}'
            });
        },
        removeLink(index) {
            this.links.splice(index, 1);
            // Reorder
            this.links.forEach((link, i) => {
                link.order = i + 1;
            });
        }
    }));
});
</script>
@endpush

