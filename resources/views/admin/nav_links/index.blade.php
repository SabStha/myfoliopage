@extends('layouts.app')
@section('title', ($nav->getTranslated('label') ?: 'Untitled').' - Sub Navigation')
@section('content')
    @if(session('status'))
    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg shadow-md">
        <p class="text-green-800 font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </p>
    </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.nav.index') }}" class="hover:text-teal-600 transition-colors inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg border border-gray-300 transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>{{ __('app.admin.nav_link.back_to_navigation') }}</span>
        </a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('admin.nav.index') }}" class="hover:text-teal-600 transition-colors text-gray-600">{{ __('app.admin.nav_link.navigation') }}</a>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium">{{ $nav->getTranslated('label') ?: 'Untitled' }}</span>
    </div>

    {{-- Header Section --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-teal-500 via-teal-600 to-cyan-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">{{ $nav->getTranslated('label') ?: 'Untitled' }}</h1>
                    <p class="text-teal-100 text-lg">{{ __('app.admin.nav_link.manage_sub_navigation') }}</p>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-sm text-teal-100">{{ __('app.admin.nav_link.total_items') }}</div>
                            <div class="text-2xl font-bold">{{ $links->count() }}</div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-sm text-teal-100">{{ __('app.admin.nav_link.categories') }}</div>
                            <div class="text-2xl font-bold">{{ $links->sum(fn($link) => $link->categories->count()) }}</div>
                        </div>
                    </div>
                </div>
                <button onclick="openNavLinkCreateModal()" class="inline-flex items-center gap-2 bg-white text-teal-600 hover:bg-teal-50 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.admin.nav_link.add_sub_nav') }}
                </button>
            </div>
        </div>
    </div>

    @if($links->isEmpty())
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="py-20 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-teal-100 to-cyan-100 mb-6">
                    <svg class="w-12 h-12 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('app.admin.nav_link.no_sub_navigation_items') }}</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">{{ __('app.admin.nav_link.get_started_description') }}</p>
                <button onclick="openNavLinkCreateModal()" class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('app.admin.nav_link.create_first_sub_nav') }}
                </button>
            </div>
        </div>
    @else
        {{-- Sub Navs Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($links as $link)
                <div class="group bg-white rounded-xl shadow-md hover:shadow-xl border border-gray-200 hover:border-teal-300 transition-all duration-300 overflow-hidden transform hover:-translate-y-1">
                    {{-- Card Header --}}
                    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-lg truncate">{{ $link->getTranslated('title') ?: 'Untitled' }}</h3>
                                    @if($link->url)
                                        <a href="{{ $link->url }}" target="_blank" class="text-xs text-teal-600 hover:text-teal-700 hover:underline flex items-center gap-1 mt-1 truncate">
                                            <span class="truncate">{{ Str::limit($link->url, 30) }}</span>
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6">
                        {{-- Categories Section --}}
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <span class="text-xs font-semibold text-gray-500 uppercase">{{ __('app.admin.nav_link.categories') }}</span>
                                <span class="text-xs text-gray-400">({{ $link->categories->count() }})</span>
                            </div>
                            @if($link->categories->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($link->categories->take(3) as $category)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gradient-to-r from-teal-100 to-cyan-100 text-teal-700 border border-teal-200">
                                            {{ $category->getTranslated('name') ?: $category->slug }}
                                        </span>
                                    @endforeach
                                    @if($link->categories->count() > 3)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            +{{ $link->categories->count() - 3 }} {{ __('app.admin.nav_link.more') }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-gray-400 italic">{{ __('app.admin.nav_link.no_categories_assigned') }}</p>
                            @endif
                        </div>

                        {{-- Stats Section --}}
                        <div class="grid grid-cols-2 gap-3 mb-4 pt-4 border-t border-gray-100">
                            <div class="text-center p-2 bg-gray-50 rounded-lg">
                                <div class="text-lg font-bold text-gray-900">{{ $link->position ?? 0 }}</div>
                                <div class="text-xs text-gray-500">{{ __('app.admin.nav_link.position') }}</div>
                            </div>
                            @if($link->progress !== null)
                                <div class="text-center p-2 bg-gray-50 rounded-lg">
                                    <div class="text-lg font-bold text-gray-900">{{ $link->progress }}%</div>
                                    <div class="text-xs text-gray-500">{{ __('app.admin.nav_link.progress') }}</div>
                                </div>
                            @else
                                <div class="text-center p-2 bg-gray-50 rounded-lg">
                                    <div class="text-lg font-bold text-gray-900">-</div>
                                    <div class="text-xs text-gray-500">{{ __('app.admin.nav_link.progress') }}</div>
                                </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                            <a href="{{ route('admin.nav.links.categories.index', [$nav, $link]) }}" 
                               class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg font-medium text-sm transition-all shadow-md hover:shadow-lg transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                {{ __('app.admin.nav_link.categories') }}
                            </a>
                            <button onclick="openEditModal({{ $link->id }})" 
                                    class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium text-sm transition-all shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <form action="{{ route('admin.nav.links.destroy', [$nav, $link]) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('app.admin.nav_link.delete_confirm') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-medium text-sm transition-all shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Edit Modal --}}
    <div id="edit-link-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full my-4 max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-xl z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ __('app.admin.nav_link.edit_nav_link_title') }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ __('app.admin.nav_link.update_navigation_link_details') }}</p>
                    </div>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="edit-link-modal-content" class="p-6">
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <svg class="animate-spin h-8 w-8 text-teal-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-gray-600">{{ __('app.admin.nav_link.loading_form') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(linkId) {
            const modal = document.getElementById('edit-link-modal');
            const modalContent = document.getElementById('edit-link-modal-content');
            
            // Show modal with loading state
            modal.classList.remove('hidden');
            modalContent.innerHTML = `
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <svg class="animate-spin h-8 w-8 text-teal-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-gray-600">{{ __('app.admin.nav_link.loading_form') }}</p>
                    </div>
                </div>
            `;
            
            // Fetch the edit form
            fetch(`{{ route('admin.nav.links.edit', [$nav, ':link']) }}`.replace(':link', linkId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Find the form
                const form = doc.querySelector('form');
                
                if (form) {
                    const originalAction = form.action;
                    
                    // Remove breadcrumb navigation
                    const breadcrumb = doc.querySelector('.mb-6.flex.items-center.gap-2');
                    if (breadcrumb) {
                        breadcrumb.remove();
                    }
                    
                    // Remove header section
                    const header = doc.querySelector('.mb-6 h1');
                    if (header && header.closest('.mb-6')) {
                        header.closest('.mb-6').remove();
                    }
                    
                    // Remove the "Back" button from action buttons
                    const backButton = form.querySelector('a[href*="links.index"]');
                    if (backButton && backButton.closest('div')) {
                        const actionButtonsDiv = backButton.closest('div');
                        backButton.remove();
                        // Adjust flex layout if needed
                        if (actionButtonsDiv.classList.contains('flex')) {
                            actionButtonsDiv.classList.remove('justify-between');
                            actionButtonsDiv.classList.add('justify-end');
                        }
                    }
                    
                    // Get the card or form container
                    const card = form.closest('x-ui.card') || form.closest('.card') || form.parentElement;
                    const formContent = card ? card.innerHTML : form.outerHTML;
                    
                    // Set the content
                    modalContent.innerHTML = formContent;
                    
                    // Re-attach form submit handler
                    const insertedForm = modalContent.querySelector('form');
                    if (insertedForm) {
                        insertedForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            submitEditForm(insertedForm, originalAction);
                        });
                    }
                    
                    // Re-initialize Alpine.js if needed
                    setTimeout(() => {
                        if (window.Alpine) {
                            window.Alpine.initTree(modalContent);
                        }
                    }, 10);
                } else {
                    modalContent.innerHTML = '<div class="p-6 text-center text-red-600">{{ __('app.admin.nav_link.error_loading_form') }}</div>';
                }
            })
            .catch(error => {
                console.error('Error loading form:', error);
                modalContent.innerHTML = '<div class="p-6 text-center text-red-600">Error loading form. Please try again.</div>';
            });
        }
        
        function closeEditModal() {
            document.getElementById('edit-link-modal').classList.add('hidden');
        }
        
        function submitEditForm(form, originalAction) {
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.textContent : '';
            
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = '{{ __('app.admin.nav_link.saving') }}';
            }
            
            const formData = new FormData(form);
            
            fetch(originalAction, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.redirected) {
                    // Success - redirect to refresh the page
                    window.location.href = response.url;
                } else if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Form submission failed');
                }
            })
            .then(data => {
                if (data && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
                alert('{{ __('app.admin.nav_link.error_saving') }}');
            });
        }
        
        // Close modal when clicking outside
        document.getElementById('edit-link-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
        
        // Function to open nav link create modal
        function openNavLinkCreateModal() {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'nav-link-create-modal' }));
        }
    </script>
    
    <x-nav-link-create-modal :navId="$nav->id" />
@endsection
