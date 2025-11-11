<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg border border-gray-300 transition-all shadow-sm hover:shadow-md dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Back to Dashboard</span>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Book Pages</h2>
            </div>
            <a href="{{ route('admin.book-pages.create') }}" class="px-3 py-2 text-sm rounded bg-blue-600 text-white">New Book Page</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded-lg">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
            @endif
            @if(session('status'))
                <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg">
                    <p class="text-green-800">{{ session('status') }}</p>
                </div>
            @endif
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2">Title</th>
                                <th class="py-2">Book/Author</th>
                                <th class="py-2">Categories</th>
                                <th class="py-2">Read At</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookPages as $page)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2">{{ $page->title }}</td>
                                    <td class="py-2">
                                        @if($page->book_title || $page->author)
                                            {{ $page->book_title ?? '' }}@if($page->author && $page->book_title), @endif{{ $page->author ?? '' }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        @if($page->categories->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($page->categories as $category)
                                                    <span class="px-2 py-1 text-xs rounded bg-gray-200 dark:bg-gray-700">{{ $category->name }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $page->read_at ? $page->read_at->format('Y-m-d') : '—' }}</td>
                                    <td class="py-2 text-right space-x-2">
                                        <a href="{{ route('admin.book-pages.edit', $page) }}" class="text-blue-600 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('admin.book-pages.destroy', $page) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline" onclick="return confirm('Delete this book page?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="py-4" colspan="5">No book pages yet. <a href="{{ route('admin.book-pages.create') }}" class="text-blue-600 hover:underline">Create your first book page</a></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($bookPages->hasPages())
                        <div class="mt-4">{{ $bookPages->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

