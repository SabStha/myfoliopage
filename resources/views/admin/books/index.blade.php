@extends('layouts.app')
@section('title','Books & Notes')
@section('content')
<x-ui.card>
  <div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-4">
      <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg border border-gray-300 transition-all shadow-sm hover:shadow-md">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Back to Dashboard</span>
      </a>
      <h2 class="font-semibold text-lg">Books & Notes</h2>
    </div>
    <a href="{{ route('admin.books.create') }}" class="px-3 py-2 text-sm rounded bg-teal-600 text-white">Add Book</a>
  </div>
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left border-b border-gray-200">
        <th class="py-2">Title</th>
        <th class="py-2">Author</th>
        <th class="py-2">Progress</th>
        <th class="py-2">Finished</th>
        <th class="py-2"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($books as $book)
        <tr class="border-b border-gray-100">
          <td class="py-2">{{ $book->title }}</td>
          <td class="py-2">{{ $book->author }}</td>
          <td class="py-2">{{ $book->progress }}</td>
          <td class="py-2">{{ $book->finished_at }}</td>
          <td class="py-2 text-right space-x-2">
            <a href="{{ route('admin.books.edit', $book) }}" class="text-amber-700">Edit</a>
            <form method="POST" action="{{ route('admin.books.destroy', $book) }}" class="inline">
              @csrf
              @method('DELETE')
              <button class="text-red-600" onclick="return confirm('Delete this book?')">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td class="py-4" colspan="5">No books yet.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="mt-4">{{ $books->links() }}</div>
</x-ui.card>
@endsection


