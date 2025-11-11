<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Loading...') }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-center min-h-[60vh]">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Opening content...</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Open modal immediately when page loads
        document.addEventListener('DOMContentLoaded', function() {
            if (window.openContentModal) {
                window.openContentModal('{{ $type }}', '{{ $id }}', '{{ $slug ?? '' }}');
                // Redirect to home after a short delay to allow modal to open
                setTimeout(function() {
                    window.location.href = '{{ route("home") }}';
                }, 500);
            } else {
                // Fallback: redirect to home if modal function not available
                setTimeout(function() {
                    window.location.href = '{{ route("home") }}';
                }, 100);
            }
        });
    </script>
</x-app-layout>

