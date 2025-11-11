@extends('layouts.app')
@section('title','Add Book')
@section('content')
<x-ui.card>
  <form method="POST" action="{{ route('admin.books.store') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm mb-1">Title</label>
      <input name="title" class="w-full rounded border-gray-300" required />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Author</label>
        <input name="author" class="w-full rounded border-gray-300" />
      </div>
      <div>
        <label class="block text-sm mb-1">Progress</label>
        <input name="progress" class="w-full rounded border-gray-300" placeholder="e.g., 40%" />
      </div>
    </div>
    <div>
      <label class="block text-sm mb-1">Finished At</label>
      <input type="date" name="finished_at" class="w-full rounded border-gray-300" />
    </div>
    <div>
      <label class="block text-sm mb-1">Notes</label>
      <textarea name="notes" class="w-full rounded border-gray-300"></textarea>
    </div>
    <div class="flex justify-end gap-2">
      <a href="{{ route('admin.books.index') }}" class="px-3 py-2 text-sm rounded border">Cancel</a>
      <button class="px-3 py-2 text-sm rounded bg-teal-600 text-white">Create</button>
    </div>
  </form>
</x-ui.card>
@endsection


