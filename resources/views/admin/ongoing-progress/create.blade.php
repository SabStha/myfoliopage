@extends('layouts.app')
@section('title','Add Progress Item')
@section('content')
<x-ui.card>
  <form method="POST" action="{{ route('admin.ongoing-progress.store') }}" class="space-y-4">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Label <span class="text-red-500">*</span></label>
        <input name="label" class="w-full rounded border-gray-300" value="{{ old('label') }}" placeholder="e.g., TryHackMe" required />
        <p class="text-xs text-gray-500 mt-1">The main category name</p>
      </div>
      <div>
        <label class="block text-sm mb-1">Unit <span class="text-red-500">*</span></label>
        <input name="unit" class="w-full rounded border-gray-300" value="{{ old('unit') }}" placeholder="e.g., rooms, hours, pages" required />
        <p class="text-xs text-gray-500 mt-1">Unit of measurement</p>
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Current Value <span class="text-red-500">*</span></label>
        <input type="number" name="value" class="w-full rounded border-gray-300" value="{{ old('value', 0) }}" min="0" required />
      </div>
      <div>
        <label class="block text-sm mb-1">Goal <span class="text-red-500">*</span></label>
        <input type="number" name="goal" class="w-full rounded border-gray-300" value="{{ old('goal', 100) }}" min="1" required />
      </div>
    </div>
    <div>
      <label class="block text-sm mb-1">Link</label>
      <input type="url" name="link" class="w-full rounded border-gray-300" value="{{ old('link') }}" placeholder="https://..." />
      <p class="text-xs text-gray-500 mt-1">Optional external link</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">ETA</label>
        <input name="eta" class="w-full rounded border-gray-300" value="{{ old('eta') }}" placeholder="e.g., Jan 2026" />
        <p class="text-xs text-gray-500 mt-1">Estimated completion date</p>
      </div>
      <div>
        <label class="block text-sm mb-1">Order</label>
        <input type="number" name="order" class="w-full rounded border-gray-300" value="{{ old('order', 0) }}" min="0" />
        <p class="text-xs text-gray-500 mt-1">Display order (lower appears first)</p>
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Trend Amount</label>
        <input type="number" name="trend_amount" class="w-full rounded border-gray-300" value="{{ old('trend_amount') }}" />
        <p class="text-xs text-gray-500 mt-1">Amount of progress in trend window</p>
      </div>
      <div>
        <label class="block text-sm mb-1">Trend Window</label>
        <input name="trend_window" class="w-full rounded border-gray-300" value="{{ old('trend_window') }}" placeholder="e.g., 30d, 7d" />
        <p class="text-xs text-gray-500 mt-1">Time period for trend</p>
      </div>
    </div>
    <div class="flex justify-end gap-2">
      <a href="{{ route('admin.ongoing-progress.index') }}" class="px-4 py-2 text-sm rounded border border-gray-300 hover:bg-gray-50">Cancel</a>
      <button class="px-4 py-2 text-sm rounded bg-teal-600 text-white hover:bg-teal-700">Create</button>
    </div>
  </form>
</x-ui.card>
@endsection













