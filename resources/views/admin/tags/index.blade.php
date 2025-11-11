<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tags</h2>
            <a href="{{ route('admin.tags.create') }}" class="px-3 py-2 text-sm rounded bg-blue-600 text-white">New Tag</a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2">Name</th>
                                <th class="py-2">Slug</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tags as $tag)
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="py-2">{{ $tag->name }}</td>
                                    <td class="py-2">{{ $tag->slug }}</td>
                                    <td class="py-2 text-right space-x-2">
                                        <a href="{{ route('admin.tags.edit', $tag) }}" class="text-blue-600 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline" onclick="return confirm('Delete this tag?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="py-4" colspan="3">No tags yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $tags->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>








