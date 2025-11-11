@extends('layouts.app')
@section('title','Udemy')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div class="text-xl font-semibold">Udemy Courses</div>
        <a href="{{ route('admin.certificates.create') }}" class="inline-flex items-center rounded-lg border border-teal-600/40 bg-teal-500/10 px-3 py-1.5 text-sm text-teal-300 hover:bg-teal-500/20">Add Certificate</a>
    </div>
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-left">
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Provider</th>
                        <th class="py-2 pr-4">Issued</th>
                        <th class="py-2 pr-4">Verify</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($certificates ?? []) as $cert)
                        <tr class="border-b border-slate-800">
                            <td class="py-3 pr-4">{{ $cert->title }}</td>
                            <td class="py-3 pr-4">{{ $cert->provider }}</td>
                            <td class="py-3 pr-4">{{ $cert->issued_at }}</td>
                            <td class="py-3 pr-4">
                                @if($cert->verify_url)
                                    <a href="{{ $cert->verify_url }}" target="_blank" class="text-teal-300 hover:underline">View</a>
                                @endif
                            </td>
                            <td class="py-3">
                                <a href="{{ route('admin.certificates.edit', $cert) }}" class="text-blue-400 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ ($certificates ?? collect())->links() }}</div>
        </div>
    </x-ui.card>
@endsection

