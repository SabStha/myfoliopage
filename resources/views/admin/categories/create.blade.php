@extends('layouts.app')
@section('title','Create Category')
@section('content')
    <x-ui.card>
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 text-red-700">{{ $errors->first() }}</div>
        @endif
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Create New Category</h3>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1 font-medium">Name *</label>
                        <input name="name" class="w-full rounded border-slate-300" value="{{ old('name') }}" required placeholder="e.g., Java, Python, JavaScript" />
                        <p class="mt-1 text-xs text-gray-400">Category name for grouping items</p>
                    </div>
                    <div>
                        <label class="block text-sm mb-1 font-medium">Slug (optional)</label>
                        <input name="slug" class="w-full rounded border-slate-300" value="{{ old('slug') }}" placeholder="auto-generated from name" />
                        <p class="mt-1 text-xs text-gray-400">URL-friendly identifier</p>
                    </div>
                    <div>
                        <label class="block text-sm mb-1 font-medium">Color (optional)</label>
                        <input name="color" class="w-full rounded border-slate-300" placeholder="#f59e0b" value="{{ old('color') }}" />
                        <p class="mt-1 text-xs text-gray-400">Hex color or Tailwind class</p>
                    </div>
                    <div>
                        <label class="block text-sm mb-1 font-medium">Position</label>
                        <input type="number" name="position" class="w-full rounded border-slate-300" value="{{ old('position', 0) }}" />
                        <p class="mt-1 text-xs text-gray-400">Display order (lower = first)</p>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-4 border-t border-slate-700">
                    <a href="{{ route('admin.categories.index') }}" class="text-gray-400 hover:text-gray-300">Cancel</a>
                    <x-ui.button type="submit">Create Category</x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.card>
@endsection

