@extends('layouts.app')
@section('title','Edit Category')
@section('content')
    <x-ui.card>
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 text-red-700">{{ $errors->first() }}</div>
        @endif
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Edit Category Details</h3>
            <form method="POST" action="{{ route('admin.categories.update',$category) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1 font-medium">Name</label>
                        <input name="name" class="w-full rounded border-slate-300" value="{{ old('name',$category->name) }}" required />
                    </div>
                    <div>
                        <label class="block text-sm mb-1 font-medium">Slug (optional)</label>
                        <input name="slug" class="w-full rounded border-slate-300" value="{{ old('slug',$category->slug) }}" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1 font-medium">Color (hex or tailwind)</label>
                        <input name="color" class="w-full rounded border-slate-300" value="{{ old('color',$category->color) }}" placeholder="#f59e0b" />
                    </div>
                    <div>
                        <label class="block text-sm mb-1 font-medium">Position</label>
                        <input type="number" name="position" class="w-full rounded border-slate-300" value="{{ old('position',$category->position) }}" />
                    </div>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-700">
                    <a href="{{ route('admin.categories.index') }}" class="text-gray-400 hover:text-gray-300">Cancel</a>
                    <x-ui.button type="submit">Save Changes</x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.card>
    
    {{-- Show NavLinks in this Category --}}
    @if($category->navLinksMany->isNotEmpty())
        <x-ui.card class="mt-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Items in this Category ({{ $category->navLinksMany->count() }})</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 text-left">
                            <th class="py-2 pr-4">Title</th>
                            <th class="py-2 pr-4">Section</th>
                            <th class="py-2 pr-4">Progress</th>
                            <th class="py-2 pr-4">Image</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->navLinksMany as $link)
                            <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                                <td class="py-3 pr-4 font-medium">{{ $link->title }}</td>
                                <td class="py-3 pr-4 text-gray-400">
                                    @if($link->navItem)
                                        <a href="{{ route('admin.nav.links.index', $link->navItem) }}" class="text-blue-400 hover:text-blue-300 hover:underline">
                                            {{ $link->navItem->label }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">Unknown</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if($link->progress)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $link->progress }}%
                                        </span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if($link->image_path)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Has Image
                                        </span>
                                    @else
                                        <span class="text-gray-500 text-xs">No image</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($link->navItem)
                                        <a href="{{ route('admin.nav.links.edit', [$link->navItem, $link]) }}" class="inline-flex items-center text-blue-400 hover:text-blue-300 hover:underline transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                    @else
                                        <span class="text-gray-500 text-xs">No NavItem</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @else
        <x-ui.card class="mt-6">
            <div class="py-8 text-center">
                <p class="text-gray-500 mb-2">No items in this category yet.</p>
                <p class="text-xs text-gray-400">Add items to this category from the Navigation → Manage Items page.</p>
            </div>
        </x-ui.card>
    @endif
@endsection

