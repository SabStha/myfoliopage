@extends('layouts.app')
@section('title','TryHackMe')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div class="text-xl font-semibold">TryHackMe Rooms</div>
        <a href="{{ route('admin.labs.create') }}" class="inline-flex items-center rounded-lg border border-teal-600/40 bg-teal-500/10 px-3 py-1.5 text-sm text-teal-300 hover:bg-teal-500/20">Add Room</a>
    </div>
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-left">
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Platform</th>
                        <th class="py-2 pr-4">Completed</th>
                        <th class="py-2 pr-4">Room</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($labs ?? []) as $lab)
                        <tr class="border-b border-slate-800">
                            <td class="py-3 pr-4">{{ $lab->title }}</td>
                            <td class="py-3 pr-4">{{ $lab->platform }}</td>
                            <td class="py-3 pr-4">{{ $lab->completed_at }}</td>
                            <td class="py-3 pr-4">
                                @if($lab->room_url)
                                    <a href="{{ $lab->room_url }}" target="_blank" class="text-teal-300 hover:underline">View</a>
                                @endif
                            </td>
                            <td class="py-3">
                                <a href="{{ route('admin.labs.edit', $lab) }}" class="text-blue-400 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ ($labs ?? collect())->links() }}</div>
        </div>
    </x-ui.card>
@endsection

