@extends('layouts.app')
@section('title','Categories')
@section('content')
    @if(session('status'))
    <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
        <p class="text-green-800 font-medium">{{ session('status') }}</p>
    </div>
    @endif
    
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold">Categories</h2>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center rounded-lg border border-teal-600/40 bg-teal-500/10 px-3 py-1.5 text-sm text-teal-300 hover:bg-teal-500/20">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Category
        </a>
    </div>
    
    @if($categories->isEmpty())
    <x-ui.card>
        <div class="py-12 text-center">
            <p class="text-gray-500 mb-4">No categories yet.</p>
            <a href="{{ route('admin.categories.create') }}" class="text-teal-400 hover:text-teal-300 hover:underline">Add your first category</a>
        </div>
    </x-ui.card>
    @else
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-left">
                        <th class="py-3 pr-4">Name</th>
                        <th class="py-3 pr-4">Slug</th>
                        <th class="py-3 pr-4">Items</th>
                        <th class="py-3 pr-4">Color</th>
                        <th class="py-3 pr-4">Position</th>
                        <th class="py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $c)
                        <tr class="border-b border-slate-800 hover:bg-slate-800/50 transition-colors">
                            <td class="py-3 pr-4 font-semibold">{{ $c->name }}</td>
                            <td class="py-3 pr-4 text-gray-400">{{ $c->slug }}</td>
                            <td class="py-3 pr-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $c->nav_links_many_count > 0 ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $c->nav_links_many_count ?? 0 }} items
                                </span>
                            </td>
                            <td class="py-3 pr-4">
                                @if($c->color)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-6 h-6 rounded border border-slate-600" style="background-color: {{ $c->color }}"></span>
                                        <span class="text-xs text-gray-400">{{ $c->color }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-500 text-xs">No color</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4">{{ $c->position ?? 0 }}</td>
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.categories.edit', $c) }}" class="inline-flex items-center text-blue-400 hover:text-blue-300 hover:underline transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    @if($c->nav_links_many_count == 0)
                                        <form action="{{ route('admin.categories.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center text-red-400 hover:text-red-300 hover:underline transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-500" title="Cannot delete category with items">Delete</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 border-t border-slate-700 pt-4">
            {{ $categories->links() }}
        </div>
    </x-ui.card>
    @endif
@endsection

