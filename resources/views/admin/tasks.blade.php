@extends('layouts.app')
@section('title','Tasks')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div class="text-xl font-semibold">Tasks (Projects)</div>
        <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center rounded-lg border border-teal-600/40 bg-teal-500/10 px-3 py-1.5 text-sm text-teal-300 hover:bg-teal-500/20">Add Project</a>
    </div>
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-left">
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Tech</th>
                        <th class="py-2 pr-4">Repo</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($projects ?? []) as $p)
                        <tr class="border-b border-slate-800">
                            <td class="py-3 pr-4">{{ $p->title }}</td>
                            <td class="py-3 pr-4">{{ $p->tech_stack }}</td>
                            <td class="py-3 pr-4">
                                @if($p->repo_url)
                                    <a href="{{ $p->repo_url }}" target="_blank" class="text-teal-300 hover:underline">Repo</a>
                                @endif
                            </td>
                            <td class="py-3">
                                <a href="{{ route('admin.projects.edit', $p) }}" class="text-blue-400 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ ($projects ?? collect())->links() }}</div>
        </div>
    </x-ui.card>
@endsection

