@props(['variant' => 'primary'])
@php
    $base = 'inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2';
    $styles = [
        'primary' => 'bg-gradient-to-r from-amber-600 to-orange-500 text-white hover:from-amber-500 hover:to-orange-500 focus:ring-amber-400 focus:ring-offset-white',
        'outline' => 'border border-slate-300 text-slate-700 hover:bg-slate-50 focus:ring-amber-300 focus:ring-offset-white',
        'ghost' => 'text-slate-600 hover:bg-amber-50',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus:ring-red-400 focus:ring-offset-white',
    ][$variant] ?? '';
@endphp
<button {{ $attributes->merge(['class' => $base.' '.$styles]) }}>
    {{ $slot }}
</button>

