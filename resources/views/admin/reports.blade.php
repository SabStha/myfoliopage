@extends('layouts.app')
@section('title','Reports')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div class="text-xl font-semibold">Timeline Reports</div>
        <a href="{{ route('admin.timeline.create') }}" class="inline-flex items-center rounded-lg border border-teal-600/40 bg-teal-500/10 px-3 py-1.5 text-sm text-teal-300 hover:bg-teal-500/20">Add Entry</a>
    </div>
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-left">
                        <th class="py-2 pr-4">Date</th>
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($entries ?? []) as $entry)
                        <tr class="border-b border-slate-800">
                            <td class="py-3 pr-4">{{ $entry->occurred_at }}</td>
                            <td class="py-3 pr-4">{{ $entry->title }}</td>
                            <td class="py-3">
                                <a href="{{ route('admin.timeline.edit', $entry) }}" class="text-blue-400 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ ($entries ?? collect())->links() }}</div>
        </div>
    </x-ui.card>
@endsection

