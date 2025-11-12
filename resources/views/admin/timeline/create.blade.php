<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New Timeline Entry</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.timeline.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm mb-1">Title</label>
                            <input name="title" class="w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Date</label>
                            <input type="date" name="occurred_at" class="w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Description</label>
                            <textarea name="description" class="w-full rounded border-gray-300"></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.timeline.index') }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
                            <button class="px-3 py-2 text-sm rounded bg-blue-600 text-white">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>















