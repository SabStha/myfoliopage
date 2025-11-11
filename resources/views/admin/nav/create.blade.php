@extends('layouts.app')
@section('title', __('app.admin.nav_link.add_nav_link'))
@section('content')
    <x-ui.card>
        <form method="POST" action="{{ route('admin.nav.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <x-dual-language-input 
                        name="label" 
                        label="{{ __('app.admin.nav_link.label') }}" 
                        :value="old('label', ['en' => '', 'ja' => ''])"
                        :placeholder="__('app.admin.nav_link.label_placeholder')"
                        :required="true"
                    />
                </div>
                <div>
                    <label class="block text-sm mb-1">{{ __('app.admin.nav_link.position') }}</label>
                    <input type="number" name="position" class="w-full rounded border-slate-700 bg-slate-900/60" value="0" />
                </div>
                <div class="flex items-center gap-2 mt-6">
                    <input type="checkbox" name="visible" value="1" checked class="h-4 w-4" />
                    <span class="text-sm">{{ __('app.admin.nav_link.visible') }}</span>
                </div>
            </div>
            <p class="text-xs text-gray-400">{{ __('app.admin.nav_link.route_detected') }}</p>
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.nav.index') }}" class="px-3 py-2 text-sm rounded border border-slate-700">{{ __('app.admin.nav_link.cancel') }}</a>
                <button class="px-3 py-2 text-sm rounded bg-teal-600 text-white">{{ __('app.admin.nav_link.create') }}</button>
            </div>
        </form>
    </x-ui.card>
@endsection

