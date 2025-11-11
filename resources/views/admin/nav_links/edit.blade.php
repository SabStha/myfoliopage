@extends('layouts.app')
@section('title','Edit Item in '.($nav->getTranslated('label') ?: 'Untitled'))
@section('content')
    <div x-data="{ showDelete: false }">
        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('admin.nav.index') }}" class="hover:text-teal-600 transition-colors">Navigation</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('admin.nav.links.index', $nav) }}" class="hover:text-teal-600 transition-colors">{{ $nav->getTranslated('label') ?: 'Untitled' }}</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Edit: {{ $link->getTranslated('title') ?: 'Untitled' }}</span>
        </div>

        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Item</h1>
            <p class="text-sm text-gray-600 mt-1">Update the details for "{{ $link->getTranslated('title') ?: 'Untitled' }}"</p>
        </div>

        <x-ui.card>
            <form method="POST" action="{{ route('admin.nav.links.update', [$nav, $link]) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Basic Information Section --}}
                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                        <p class="text-sm text-gray-500 mt-1">Essential details about this item</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <x-dual-language-input 
                                name="title" 
                                label="Title" 
                                :value="$link->getTranslations('title')"
                                placeholder="Enter item title"
                                :required="true"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Progress (0-100)
                            </label>
                            <input 
                                type="number" 
                                name="progress" 
                                min="0" 
                                max="100" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors" 
                                value="{{ old('progress', $link->progress) }}"
                                placeholder="0-100"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Issued At
                            </label>
                            <input 
                                type="date" 
                                name="issued_at" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors" 
                                value="{{ old('issued_at', optional($link->issued_at)->format('Y-m-d')) }}"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Position
                            </label>
                            <input 
                                type="number" 
                                name="position" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors" 
                                value="{{ old('position', $link->position) }}"
                                placeholder="Display order"
                            />
                        </div>
                    </div>
                </div>

                {{-- Categories Section --}}
                <div class="space-y-6 pt-6 border-t border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">Categories</h2>
                        <p class="text-sm text-gray-500">Select categories that apply to this item</p>
                    </div>

                    @if($categories->isEmpty())
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                No categories available for this navigation. 
                                <a href="{{ route('admin.nav.links.categories.index', [$nav, $link]) }}" class="font-medium underline hover:text-blue-900">
                                    Manage categories here
                                </a>
                            </p>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 max-h-64 overflow-y-auto">
                            <div class="space-y-2">
                                @php
                                    $selectedCategories = old('categories', $link->categories->pluck('id')->toArray() ?? []);
                                @endphp
                                @foreach($categories as $cat)
                                    <label class="flex items-center gap-3 p-3 bg-white rounded-lg border border-gray-200 hover:border-teal-300 hover:bg-teal-50/50 cursor-pointer transition-all group">
                                        <input 
                                            type="checkbox" 
                                            name="categories[]" 
                                            value="{{ $cat->id }}" 
                                            class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 focus:ring-2" 
                                            @checked(in_array($cat->id, $selectedCategories))
                                        />
                                        <span class="flex-1 text-sm font-medium text-gray-700 group-hover:text-teal-700">
                                            {{ $cat->getTranslated('name') ?: $cat->slug }}
                                        </span>
                                        @if($cat->color)
                                            <span class="inline-block w-4 h-4 rounded-full shadow-sm" style="background-color: {{ $cat->color }}"></span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Media Section --}}
                <div class="space-y-6 pt-6 border-t border-gray-200">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">Media & Files</h2>
                        <p class="text-sm text-gray-500">Upload images and documents</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Image Upload --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Image (optional)
                            </label>
                            <div class="space-y-3">
                                <input 
                                    type="file" 
                                    name="image" 
                                    accept="image/*" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                                />
                                @if($link->image_path)
                                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <img src="{{ asset('storage/'.$link->image_path) }}" alt="Current image" class="w-20 h-20 rounded-lg object-cover border border-gray-200" />
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-700">Current Image</p>
                                            <p class="text-xs text-gray-500">Upload a new image to replace</p>
                                        </div>
                                        <label class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-red-600 hover:text-red-700 cursor-pointer border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                            <input type="checkbox" name="remove_image" value="1" class="rounded border-red-300 text-red-600 focus:ring-red-500" />
                                            Remove
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- PDF Upload --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                PDF Document (optional)
                            </label>
                            <div class="space-y-3">
                                <input 
                                    type="file" 
                                    name="document" 
                                    accept="application/pdf" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100"
                                />
                                @if($link->document_path)
                                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <a href="{{ asset('storage/'.$link->document_path) }}" target="_blank" class="text-sm font-medium text-teal-600 hover:text-teal-700 hover:underline">
                                                View Current PDF
                                            </a>
                                            <p class="text-xs text-gray-500">Click to open in new tab</p>
                                        </div>
                                        <label class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-red-600 hover:text-red-700 cursor-pointer border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                            <input type="checkbox" name="remove_document" value="1" class="rounded border-red-300 text-red-600 focus:ring-red-500" />
                                            Remove
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                    <a 
                        href="{{ route('admin.nav.links.index', $nav) }}" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back
                    </a>

                    <div class="flex items-center gap-3">
                        <button 
                            type="button" 
                            @click="showDelete = true" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                        <button 
                            type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition-colors shadow-sm hover:shadow"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </x-ui.card>

        {{-- Delete Confirmation Modal --}}
        <div 
            x-show="showDelete" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            style="display: none;"
        >
            <div 
                @click.away="showDelete = false" 
                class="w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Delete Item?</h3>
                            <p class="text-sm text-gray-600">This action cannot be undone.</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-700 mb-6">
                        Are you sure you want to delete "<strong>{{ $link->getTranslated('title') ?: 'Untitled' }}</strong>"? This will permanently remove the item and all associated data.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button 
                            type="button" 
                            @click="showDelete = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <form method="POST" action="{{ route('admin.nav.links.destroy', [$nav, $link]) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                            >
                                Delete Item
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
