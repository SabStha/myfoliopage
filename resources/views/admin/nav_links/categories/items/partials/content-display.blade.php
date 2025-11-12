<div class="p-6">
    <p class="text-xs text-amber-600 mb-4">
        <strong>{{ __('app.common.note') }}:</strong> {{ __('app.admin.categories.note_items_linking') }}
    </p>

    {{-- Content Items by Type --}}
    <div class="space-y-6">
        {{-- Book Pages --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.categories.book_pages') }}</h3>
                            <p class="text-sm text-gray-600">{{ $bookPages->count() }} {{ __('app.admin.categories.items_count') }}</p>
                        </div>
                    </div>
                    <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors border border-blue-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.admin.categories.add_items') }}
                    </button>
                </div>
            </div>
            <div class="p-6">
                @if($bookPages->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">{{ __('app.admin.categories.no_book_pages_yet') }}</p>
                        <div class="flex items-center justify-center gap-3">
                            <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition-colors border border-blue-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.add_existing_items') }}
                            </button>
                            <button onclick="openCreateModal('book-page')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.create_new') }}
                            </button>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($bookPages as $bookPage)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ $bookPage->title }}</h4>
                                    <div class="flex items-center gap-2">
                                        <button onclick="openEditContentModal('book-page', '{{ $bookPage->slug }}')" class="text-blue-600 hover:text-blue-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.sections.detach', $item) }}" method="POST" class="inline" id="remove-book-page-{{ $bookPage->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="sectionable_type" value="App\Models\BookPage">
                                            <input type="hidden" name="sectionable_id" value="{{ $bookPage->id }}">
                                            <button type="button" class="text-red-600 hover:text-red-700" onclick="openRemoveItemModal(document.getElementById('remove-book-page-{{ $bookPage->id }}'))">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @if($bookPage->summary)
                                    <p class="text-sm text-gray-600 line-clamp-2 mb-2">{{ Str::limit($bookPage->summary, 100) }}</p>
                                @endif
                                @if($bookPage->sections && $bookPage->sections->isNotEmpty())
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <p class="text-xs text-gray-500 mb-1">{{ __('app.admin.categories.sections_label') }}</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($bookPage->sections as $section)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $section->id === $item->id ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-700' }}" title="{{ $section->title }}">
                                                    {{ $section->title }}
                                                    @if($section->id === $item->id)
                                                        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-center gap-3 mt-4 pt-4 border-t border-gray-200">
                        <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition-colors border border-blue-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.add_existing_items') }}
                        </button>
                        <button onclick="openCreateModal('book-page')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.create_new') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Code Summaries --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.categories.code_summaries') }}</h3>
                            <p class="text-sm text-gray-600">{{ $codeSummaries->count() }} {{ __('app.admin.categories.items_count') }}</p>
                        </div>
                    </div>
                    <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-purple-600 hover:text-purple-700 hover:bg-purple-50 transition-colors border border-purple-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.admin.categories.add_items') }}
                    </button>
                </div>
            </div>
            <div class="p-6">
                @if($codeSummaries->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">{{ __('app.admin.categories.no_code_summaries_yet') }}</p>
                        <div class="flex items-center justify-center gap-3">
                            <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 text-sm font-medium rounded-lg transition-colors border border-purple-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.add_existing_items') }}
                            </button>
                            <button onclick="openCreateModal('code-summary')" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.create_new') }}
                            </button>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($codeSummaries as $codeSummary)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ $codeSummary->getTranslated('title') ?: $codeSummary->slug }}</h4>
                                    <div class="flex items-center gap-2">
                                        <button onclick="openEditContentModal('code-summary', '{{ $codeSummary->slug }}')" class="text-blue-600 hover:text-blue-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.sections.detach', $item) }}" method="POST" class="inline" id="remove-code-summary-{{ $codeSummary->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="sectionable_type" value="App\Models\CodeSummary">
                                            <input type="hidden" name="sectionable_id" value="{{ $codeSummary->id }}">
                                            <button type="button" class="text-red-600 hover:text-red-700" onclick="openRemoveItemModal(document.getElementById('remove-code-summary-{{ $codeSummary->id }}'))">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @if($codeSummary->getTranslated('summary'))
                                    <p class="text-sm text-gray-600 line-clamp-2 mb-2">{{ Str::limit($codeSummary->getTranslated('summary'), 100) }}</p>
                                @endif
                                @if($codeSummary->sections && $codeSummary->sections->isNotEmpty())
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <p class="text-xs text-gray-500 mb-1">{{ __('app.admin.categories.sections_label') }}</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($codeSummary->sections as $section)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $section->id === $item->id ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-700' }}" title="{{ $section->title }}">
                                                    {{ $section->title }}
                                                    @if($section->id === $item->id)
                                                        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-center gap-3 mt-4 pt-4 border-t border-gray-200">
                        <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 text-sm font-medium rounded-lg transition-colors border border-purple-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.add_existing_items') }}
                        </button>
                        <button onclick="openCreateModal('code-summary')" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.create_new') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Rooms --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.categories.rooms') }}</h3>
                            <p class="text-sm text-gray-600">{{ $rooms->count() }} {{ __('app.admin.categories.items_count') }}</p>
                        </div>
                    </div>
                    <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-green-600 hover:text-green-700 hover:bg-green-50 transition-colors border border-green-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.admin.categories.add_items') }}
                    </button>
                </div>
            </div>
            <div class="p-6">
                @if($rooms->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">{{ __('app.admin.categories.no_rooms_yet') }}</p>
                        <div class="flex items-center justify-center gap-3">
                            <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 hover:bg-green-100 text-green-700 text-sm font-medium rounded-lg transition-colors border border-green-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.add_existing_items') }}
                            </button>
                            <button onclick="openCreateModal('room')" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.create_new') }}
                            </button>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($rooms as $room)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ $room->title }}</h4>
                                    <div class="flex items-center gap-2">
                                        <button onclick="openEditContentModal('room', '{{ $room->slug }}')" class="text-blue-600 hover:text-blue-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.sections.detach', $item) }}" method="POST" class="inline" id="remove-room-{{ $room->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="sectionable_type" value="App\Models\Room">
                                            <input type="hidden" name="sectionable_id" value="{{ $room->id }}">
                                            <button type="button" class="text-red-600 hover:text-red-700" onclick="openRemoveItemModal(document.getElementById('remove-room-{{ $room->id }}'))">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @if($room->sections && $room->sections->isNotEmpty())
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <p class="text-xs text-gray-500 mb-1">{{ __('app.admin.categories.sections_label') }}</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($room->sections as $section)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $section->id === $item->id ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-700' }}" title="{{ $section->title }}">
                                                    {{ $section->title }}
                                                    @if($section->id === $item->id)
                                                        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-center gap-3 mt-4 pt-4 border-t border-gray-200">
                        <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 hover:bg-green-100 text-green-700 text-sm font-medium rounded-lg transition-colors border border-green-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.add_existing_items') }}
                        </button>
                        <button onclick="openCreateModal('room')" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.create_new') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Certificates --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-yellow-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.categories.certificates') }}</h3>
                            <p class="text-sm text-gray-600">{{ $certificates->count() }} {{ __('app.admin.categories.items_count') }}</p>
                        </div>
                    </div>
                    <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-yellow-600 hover:text-yellow-700 hover:bg-yellow-50 transition-colors border border-yellow-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.admin.categories.add_items') }}
                    </button>
                </div>
            </div>
            <div class="p-6">
                @if($certificates->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">{{ __('app.admin.categories.no_certificates_yet') }}</p>
                        <div class="flex items-center justify-center gap-3">
                            <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 text-sm font-medium rounded-lg transition-colors border border-yellow-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.add_existing_items') }}
                            </button>
                            <button onclick="openCreateModal('certificate')" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.create_new') }}
                            </button>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($certificates as $certificate)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ $certificate->title }}</h4>
                                    <div class="flex items-center gap-2">
                                        <button onclick="openEditContentModal('certificate', {{ $certificate->id }})" class="text-blue-600 hover:text-blue-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.sections.detach', $item) }}" method="POST" class="inline" id="remove-certificate-{{ $certificate->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="sectionable_type" value="App\Models\Certificate">
                                            <input type="hidden" name="sectionable_id" value="{{ $certificate->id }}">
                                            <button type="button" class="text-red-600 hover:text-red-700" onclick="openRemoveItemModal(document.getElementById('remove-certificate-{{ $certificate->id }}'))">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @if($certificate->sections && $certificate->sections->isNotEmpty())
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <p class="text-xs text-gray-500 mb-1">{{ __('app.admin.categories.sections_label') }}</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($certificate->sections as $section)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $section->id === $item->id ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-700' }}" title="{{ $section->title }}">
                                                    {{ $section->title }}
                                                    @if($section->id === $item->id)
                                                        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-center gap-3 mt-4 pt-4 border-t border-gray-200">
                        <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 text-sm font-medium rounded-lg transition-colors border border-yellow-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.add_existing_items') }}
                        </button>
                        <button onclick="openCreateModal('certificate')" class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.create_new') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Courses --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="bg-gradient-to-r from-cyan-50 to-blue-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-cyan-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('app.admin.categories.courses') }}</h3>
                            <p class="text-sm text-gray-600">{{ $courses->count() }} {{ __('app.admin.categories.items_count') }}</p>
                        </div>
                    </div>
                    <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-cyan-600 hover:text-cyan-700 hover:bg-cyan-50 transition-colors border border-cyan-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('app.admin.categories.add_items') }}
                    </button>
                </div>
            </div>
            <div class="p-6">
                @if($courses->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">{{ __('app.admin.categories.no_courses_yet') }}</p>
                        <div class="flex items-center justify-center gap-3">
                            <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 text-sm font-medium rounded-lg transition-colors border border-cyan-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.add_existing_items') }}
                            </button>
                            <button onclick="openCreateModal('course')" class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('app.admin.categories.create_new') }}
                            </button>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($courses as $course)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900">{{ $course->title }}</h4>
                                    <div class="flex items-center gap-2">
                                        <button onclick="openEditContentModal('course', {{ $course->id }})" class="text-blue-600 hover:text-blue-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.sections.detach', $item) }}" method="POST" class="inline" id="remove-course-{{ $course->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="sectionable_type" value="App\Models\Course">
                                            <input type="hidden" name="sectionable_id" value="{{ $course->id }}">
                                            <button type="button" class="text-red-600 hover:text-red-700" onclick="openRemoveItemModal(document.getElementById('remove-course-{{ $course->id }}'))">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @if($course->provider)
                                    <p class="text-sm text-gray-600 mb-2">{{ $course->provider }}</p>
                                @endif
                                @if($course->sections && $course->sections->isNotEmpty())
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <p class="text-xs text-gray-500 mb-1">{{ __('app.admin.categories.sections_label') }}</p>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($course->sections as $section)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $section->id === $item->id ? 'bg-teal-100 text-teal-800' : 'bg-gray-100 text-gray-700' }}" title="{{ $section->title }}">
                                                    {{ $section->title }}
                                                    @if($section->id === $item->id)
                                                        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center justify-center gap-3 mt-4 pt-4 border-t border-gray-200">
                        <button onclick="openAddContentModal({{ $item->id }}, {{ json_encode($item->title) }})" class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 text-sm font-medium rounded-lg transition-colors border border-cyan-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.add_existing_items') }}
                        </button>
                        <button onclick="openCreateModal('course')" class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('app.admin.categories.create_new') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

