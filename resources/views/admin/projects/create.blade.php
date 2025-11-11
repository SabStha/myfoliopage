<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New Project</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm mb-1">Title</label>
                            <input name="title" class="w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Tags (comma separated)</label>
                            <input name="tags" class="w-full rounded border-gray-300" placeholder="Laravel, PHP, Security" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Cover Image</label>
                            <input type="file" name="image" accept="image/*" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Slug</label>
                            <input name="slug" class="w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Summary</label>
                            <textarea name="summary" class="w-full rounded border-gray-300"></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm mb-1">Tech Stack</label>
                                <input name="tech_stack" class="w-full rounded border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Completed At</label>
                                <input type="date" name="completed_at" class="w-full rounded border-gray-300" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm mb-1">Repo URL</label>
                                <input name="repo_url" class="w-full rounded border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm mb-1">Demo URL</label>
                                <input name="demo_url" class="w-full rounded border-gray-300" />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.projects.index') }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
                            <button class="px-3 py-2 text-sm rounded bg-blue-600 text-white">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


