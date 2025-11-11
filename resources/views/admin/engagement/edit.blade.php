@extends('layouts.app')
@section('title', __('app.admin.engagement.title'))
@section('content')
<div class="max-w-6xl mx-auto p-6" x-data="{ showResetModal: false }">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('app.admin.engagement.edit_title') }}</h1>
        <p class="text-gray-600 mt-1">{{ __('app.admin.engagement.manage_video') }}</p>
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
         class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg flex items-center justify-between">
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

    <form method="POST" action="{{ route('admin.engagement.update') }}" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Title Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.engagement.section_content') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-dual-language-input 
                    name="title" 
                    label="{{ __('app.admin.engagement.title_label') }}" 
                    :value="$engagementSection->getTranslations('title')"
                    placeholder="Discover our engagements"
                />
            </div>
        </x-card>

        {{-- Video Section --}}
        <x-card class="p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ __('app.admin.engagement.video') }}</h2>
            
            @if($videoMedia)
            <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">{{ $videoMedia->title }}</p>
                            <p class="text-sm text-gray-500">{{ $videoMedia->path }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ asset('storage/' . $videoMedia->path) }}" target="_blank" class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                            {{ __('app.admin.engagement.view_video') }}
                        </a>
                        <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 cursor-pointer text-sm font-medium">
                            <input type="checkbox" name="remove_video" value="1" class="rounded">
                            {{ __('app.admin.engagement.remove') }}
                        </label>
                    </div>
                </div>
                <video src="{{ asset('storage/' . $videoMedia->path) }}" controls class="w-full rounded-lg max-h-96 bg-black">
                    Your browser does not support the video tag.
                </video>
            </div>
            @else
            <div class="mb-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <p class="text-yellow-800 font-medium">{{ __('app.admin.engagement.no_video') }}</p>
                </div>
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.admin.engagement.upload_new_video') }}</label>
                <input type="file" name="video" accept="video/mp4,video/webm,video/ogg" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">{{ __('app.admin.engagement.supported_formats') }}</p>
                @if($videoMedia)
                <p class="text-xs text-yellow-600 mt-1">{{ __('app.admin.engagement.upload_warning') }}</p>
                @endif
            </div>
        </x-card>

        {{-- Submit Button --}}
        <div class="flex justify-between items-center gap-4">
            <button @click="showResetModal = true" type="button" class="px-6 py-2 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 font-semibold transition-colors">
                {{ __('app.admin.engagement.reset_to_defaults') }}
            </button>
            <div class="flex gap-4">
                <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">{{ __('app.common.cancel') }}</a>
                <button type="submit" class="px-6 py-2 rounded-lg bg-[#ffb400] text-gray-900 font-semibold hover:bg-[#e6a200] transition-colors">{{ __('app.admin.engagement.save_changes') }}</button>
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
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.engagement.reset_confirm_title') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('app.admin.engagement.reset_confirm_cannot_undo') }}</p>
                        </div>
                    </div>
                </div>
                
                {{-- Modal Body --}}
                <div class="px-6 py-4">
                    <p class="text-gray-700 mb-4">{{ __('app.admin.engagement.reset_confirm_message') }}</p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-sm font-semibold text-red-800 mb-2">{{ __('app.admin.engagement.reset_will') }}</p>
                        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                            <li>{{ __('app.admin.engagement.reset_title') }}</li>
                            <li>{!! __('app.admin.engagement.reset_delete_videos') !!}</li>
                        </ul>
                    </div>
                    <p class="text-sm text-gray-600">{{ __('app.admin.engagement.reset_lost_forever') }}</p>
                </div>
                
                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
                        <button @click="showResetModal = false" 
                            type="button"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium transition-colors">
                            {{ __('app.common.cancel') }}
                        </button>
                        <form method="POST" action="{{ route('admin.engagement.reset') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 font-medium transition-colors shadow-sm">
                                {{ __('app.admin.engagement.yes_reset') }}
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

