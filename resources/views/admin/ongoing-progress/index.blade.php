@extends('layouts.app')
@section('title', __('app.admin.progress.title'))
@section('content')

@if(session('status'))
<div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg shadow-lg">
  <p class="text-green-800 font-medium">{{ session('status') }}</p>
</div>
@endif

@if($errors->has('build'))
<div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400 rounded-lg shadow-lg">
  <p class="text-red-800 font-medium">{{ $errors->first('build') }}</p>
</div>
@endif

<div class="mb-4">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-xl font-semibold">{{ __('app.admin.progress.title') }}</h2>
      <p class="text-sm text-gray-600 mt-1">{{ __('app.admin.progress.description') }}</p>
    </div>
    <div class="flex items-center gap-3">
      <form method="POST" action="{{ route('admin.build') }}" id="buildForm">
        @csrf
        <button type="submit" class="px-4 py-2 bg-[#ffb400] text-gray-900 font-semibold rounded-lg hover:bg-[#e6a200] transition-colors flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          <span>{{ __('app.admin.build_assets') }}</span>
        </button>
      </form>
      <a href="{{ route('admin.nav.index') }}" class="px-4 py-2 text-sm rounded bg-teal-600 text-white hover:bg-teal-700">{{ __('app.admin.progress.manage_navigation') }}</a>
    </div>
  </div>
</div>

@if(session('info'))
  <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
    <p class="text-blue-800">{{ session('info') }}</p>
  </div>
@endif

<x-ui.card>
  <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
    <p class="text-sm text-amber-800 mb-2">
      <strong>{{ __('app.admin.progress.how_it_works') }}</strong> {{ __('app.admin.progress.how_it_works_text') }} <a href="{{ route('admin.nav.index') }}" class="underline font-semibold">{{ __('app.admin.progress.customize_sidebar_link') }}</a>{{ __('app.admin.progress.how_it_works_text2') }}
    </p>
    <p class="text-sm text-amber-800 font-semibold">
      ⚠️ <strong>{{ __('app.admin.progress.important') }}</strong> {{ __('app.admin.progress.build_assets_note') }} <strong>{{ __('app.admin.progress.build_assets_button') }}</strong> {{ __('app.admin.progress.build_assets_note2') }}
    </p>
  </div>
  
  <table class="w-full text-sm">
    <thead>
      <tr class="text-left border-b border-gray-200">
        <th class="py-3">{{ __('app.admin.progress.label') }}</th>
        <th class="py-3">{{ __('app.admin.progress.items') }}</th>
        <th class="py-3">{{ __('app.admin.progress.progress') }}</th>
        <th class="py-3">{{ __('app.admin.progress.completed') }}</th>
        <th class="py-3">{{ __('app.admin.progress.actions') }}</th>
      </tr>
    </thead>
    <tbody>
      @forelse($navItems as $item)
        <tr class="border-b border-gray-100 hover:bg-gray-50">
          <td class="py-3 font-medium">{{ $item['label'] }}</td>
          <td class="py-3 text-gray-600">{{ $item['total_items'] }} {{ $item['total_items'] == 1 ? __('app.admin.progress.item') : __('app.admin.progress.items_plural') }}</td>
          <td class="py-3">
            <div class="flex items-center gap-2">
              <div class="w-32 h-3 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-[#ffb400] transition-all duration-300" style="width: {{ min($item['progress'], 100) }}%"></div>
              </div>
              <span class="text-sm font-semibold text-gray-700">{{ $item['progress'] }}%</span>
            </div>
          </td>
          <td class="py-3 text-gray-600">{{ $item['completed_items'] }} / {{ $item['total_items'] }}</td>
          <td class="py-3">
            <a href="{{ route('admin.nav.links.index', $item['nav_item']) }}" class="text-teal-600 hover:underline font-medium">{{ __('app.admin.progress.manage_items') }}</a>
          </td>
        </tr>
      @empty
        <tr>
          <td class="py-8 text-center text-gray-500" colspan="5">
            <div class="flex flex-col items-center gap-3">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              <div>
                <p class="font-medium mb-1">{{ __('app.admin.progress.no_progress_items') }}</p>
                <p class="text-sm">{{ __('app.admin.progress.add_navitems') }} <a href="{{ route('admin.nav.index') }}" class="text-teal-600 hover:underline font-semibold">{{ __('app.admin.progress.customize_sidebar_link') }}</a> {{ __('app.admin.progress.add_navlinks') }}</p>
              </div>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</x-ui.card>

<script>
document.getElementById('buildForm').addEventListener('submit', function(e) {
  const button = this.querySelector('button[type="submit"]');
  const originalText = button.innerHTML;
  button.disabled = true;
  button.innerHTML = '<svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>{{ __('app.admin.building') }}</span>';
  
  // Re-enable after 10 seconds as fallback (build usually takes 3-5 seconds)
  setTimeout(() => {
    if (button.disabled) {
      button.disabled = false;
      button.innerHTML = originalText;
    }
  }, 10000);
});
</script>
@endsection

