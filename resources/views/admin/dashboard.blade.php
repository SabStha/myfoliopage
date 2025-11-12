@extends('layouts.app')
@section('title', __('app.admin.dashboard'))
@section('content')
<script>
  window.dashboardChartData = @json($chart);
  window.dashboardOverallData = @json($overall);
</script>

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

<div class="p-6 bg-white rounded-xl shadow border border-gray-200 space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold text-gray-800">{{ __('app.admin.dashboard') }}</h2>
    <div class="flex items-center gap-3">
      @php
        $user = Auth::user();
        $portfolioUrl = $user && $user->username ? route('portfolio.show', $user->username) : route('landing');
      @endphp
      <a href="{{ $portfolioUrl }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
        <span>{{ __('app.admin.view_public_homepage') }}</span>
      </a>
      <form method="POST" action="{{ route('admin.build') }}" id="buildForm">
        @csrf
        <button type="submit" class="px-4 py-2 bg-[#ffb400] text-gray-900 font-semibold rounded-lg hover:bg-[#e6a200] transition-colors flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          <span>{{ __('app.admin.build_assets') }}</span>
        </button>
      </form>
    </div>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @php
      // Build metric list from categories if available, otherwise from individual counts
      $metricList = isset($categories) && count($categories) > 0 ? $categories : [];
      
      // Only add fallback metrics if they have values > 0
      if (empty($metricList)) {
        if ($projects_count > 0) {
          $metricList[] = ['label' => __('app.admin.projects'), 'value' => $projects_count];
        }
        if ($certificates_count > 0) {
          $metricList[] = ['label' => __('app.admin.certificates'), 'value' => $certificates_count];
        }
        if ($labs_count > 0) {
          $metricList[] = ['label' => __('app.admin.labs'), 'value' => $labs_count];
        }
      }
    @endphp
    
    @if(count($metricList) > 0)
      @foreach($metricList as $m)
        <div class="bg-yellow-400/10 border-l-4 border-yellow-500 p-4 rounded-lg shadow">
          <p class="text-sm text-gray-600">{{ $m['label'] }}</p>
          <p class="text-2xl font-bold text-yellow-600">{{ $m['value'] }}</p>
        </div>
      @endforeach
    @else
      <div class="col-span-full p-8 text-center bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Welcome to your dashboard!</h3>
        <p class="mt-2 text-sm text-gray-500">Get started by adding your first project, certificate, or navigation item.</p>
        <div class="mt-6 flex justify-center gap-3">
          <a href="{{ route('admin.nav.index') }}" class="px-4 py-2 bg-[#ffb400] text-gray-900 font-semibold rounded-lg hover:bg-[#e6a200] transition-colors">
            Customize Sidebar
          </a>
          <a href="{{ route('admin.projects.create') }}" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
            Add Project
          </a>
        </div>
      </div>
    @endif
  </div>
</div>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
  <x-card class="p-6">
    <div class="mb-3 font-semibold">{{ __('app.admin.activity_by_month') }}</div>
    <div class="rounded-xl border border-gray-200 p-3 bg-white">
      <canvas id="roomsChart" height="140"></canvas>
    </div>
  </x-card>
  <x-card class="p-6">
    <div class="mb-3 font-semibold">{{ __('app.admin.overall_breakdown') }}</div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
      <div class="rounded-xl border border-gray-200 p-3">
        <canvas id="overallChart" height="160"></canvas>
      </div>
      <div>
        @php($list = isset($categories) && count($categories) > 0 ? $categories : (count($metricList) > 0 ? $metricList : []))
        @if(count($list) > 0)
          <ul class="space-y-2 text-sm text-gray-600">
            @foreach($list as $row)
              <li class="flex items-center justify-between"><span>{{ $row['label'] }}</span><span class="font-semibold text-gray-800">{{ $row['value'] }}</span></li>
            @endforeach
          </ul>
        @else
          <p class="text-sm text-gray-500 text-center py-4">No data available yet</p>
        @endif
      </div>
    </div>
  </x-card>
</div>

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


