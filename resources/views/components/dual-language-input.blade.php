@props(['name', 'label', 'value' => null, 'placeholder' => '', 'required' => false, 'type' => 'text', 'rows' => null])

@php
    $translations = is_array($value) ? $value : (is_string($value) && json_decode($value, true) ? json_decode($value, true) : ['en' => $value ?? '', 'ja' => '']);
    $enValue = $translations['en'] ?? '';
    $jaValue = $translations['ja'] ?? '';
    $currentLocale = app()->getLocale();
@endphp

<div class="space-y-3" 
     x-data="dualLanguageInput()"
     x-init="
         activeLang = '{{ $currentLocale }}';
         enValue = @js($enValue);
         jaValue = @js($jaValue);
     "
     x-cloak>
    <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-semibold text-gray-700">
            {{ $label }} <span class="text-red-500">{{ $required ? '*' : '' }}</span>
        </label>
        <div class="flex items-center gap-2">
            <select x-model="activeLang" class="text-xs px-2 py-1 rounded border border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="en">🇬🇧 English</option>
                <option value="ja">🇯🇵 日本語</option>
            </select>
            <span x-show="translating" class="text-xs text-gray-500 flex items-center gap-1">
                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Translating...
            </span>
        </div>
    </div>
    
    {{-- English Field --}}
    <div x-show="activeLang === 'en'" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100"
         x-cloak>
        @if($rows)
            <textarea 
                name="{{ $name }}[en]" 
                rows="{{ $rows }}"
                x-model="enValue"
                @input="handleInput($event.target.value, 'en')"
                x-bind:required="activeLang === 'en' && {{ $required ? 'true' : 'false' }}"
                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                placeholder="{{ $placeholder }}">{{ old($name . '.en', $enValue) }}</textarea>
        @else
            <input 
                type="{{ $type }}"
                name="{{ $name }}[en]" 
                x-model="enValue"
                @input="handleInput($event.target.value, 'en')"
                value="{{ old($name . '.en', $enValue) }}"
                x-bind:required="activeLang === 'en' && {{ $required ? 'true' : 'false' }}"
                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                placeholder="{{ $placeholder }}" />
        @endif
        <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_english_auto_translate') }}</p>
    </div>
    
    {{-- Japanese Field --}}
    <div x-show="activeLang === 'ja'" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100"
         x-cloak>
        @if($rows)
            <textarea 
                name="{{ $name }}[ja]" 
                rows="{{ $rows }}"
                x-model="jaValue"
                @input="handleInput($event.target.value, 'ja')"
                x-bind:required="activeLang === 'ja' && {{ $required ? 'true' : 'false' }}"
                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                placeholder="{{ $placeholder }}">{{ old($name . '.ja', $jaValue) }}</textarea>
        @else
            <input 
                type="{{ $type }}"
                name="{{ $name }}[ja]" 
                x-model="jaValue"
                @input="handleInput($event.target.value, 'ja')"
                value="{{ old($name . '.ja', $jaValue) }}"
                x-bind:required="activeLang === 'ja' && {{ $required ? 'true' : 'false' }}"
                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                placeholder="{{ $placeholder }}" />
        @endif
        <p class="text-xs text-gray-500 mt-1">{{ __('app.common.type_in_japanese_auto_translate') }}</p>
    </div>
</div>
