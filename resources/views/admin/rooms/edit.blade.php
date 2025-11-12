@extends('layouts.app')
@section('title', 'Edit Room')
@section('content')
    <script>
        window.roomEditData = {
            sections: @js($sections->map(function($s) {
                return ['id' => $s->id, 'name' => $s->title, 'title' => $s->title, 'category_id' => $s->category_id, 'category_name' => $s->category->name];
            })),
            selectedCategories: @js($room->categories->pluck('id')->toArray())
        };
        
        // Register Alpine.js component data - works for both normal page load and modal load
        window.registerRoomEditComponent = function() {
            if (window.Alpine && window.Alpine.data) {
                try {
                    window.Alpine.data('roomEdit', () => ({
                        sections: window.roomEditData?.sections || [],
                        selectedCategories: window.roomEditData?.selectedCategories || [],
                        showNewSectionForm: false,
                        newSectionName: '',
                        newSectionCategoryId: '',
                        newSectionDescription: '',
                        loading: false,
                        autoSlug: false,
                        previewData: {
                            title: @js(old('title', $room->title ?? '')),
                            summary: @js(old('summary', $room->summary ?? '')),
                            platform: @js(old('platform', $room->platform ?? '')),
                            difficulty: @js(old('difficulty', $room->difficulty ?? '')),
                            room_url: @js(old('room_url', $room->room_url ?? '')),
                            objective_goal: @js(old('objective_goal', $room->objective_goal ?? '')),
                            key_techniques_used: @js(old('key_techniques_used', $room->key_techniques_used ?? '')),
                            tools_commands_used: @js(old('tools_commands_used', $room->tools_commands_used ?? '')),
                            attack_vector_summary: @js(old('attack_vector_summary', $room->attack_vector_summary ?? '')),
                            flag_evidence_proof: @js(old('flag_evidence_proof', $room->flag_evidence_proof ?? '')),
                            time_spent: @js(old('time_spent', $room->time_spent ?? '')),
                            reflection_takeaways: @js(old('reflection_takeaways', $room->reflection_takeaways ?? '')),
                            difficulty_confirmation: @js(old('difficulty_confirmation', $room->difficulty_confirmation ?? '')),
                            walkthrough_summary_steps: @js(old('walkthrough_summary_steps', $room->walkthrough_summary_steps ?? '')),
                            tools_environment: @js(old('tools_environment', $room->tools_environment ?? '')),
                            command_log_snippet: @js(old('command_log_snippet', $room->command_log_snippet ?? '')),
                            room_id_author: @js(old('room_id_author', $room->room_id_author ?? '')),
                            completion_screenshot_report_link: @js(old('completion_screenshot_report_link', $room->completion_screenshot_report_link ?? '')),
                            platform_username: @js(old('platform_username', $room->platform_username ?? '')),
                            platform_profile_link: @js(old('platform_profile_link', $room->platform_profile_link ?? '')),
                            status: @js(old('status', $room->status ?? 'in_progress')),
                            score_points_earned: @js(old('score_points_earned', $room->score_points_earned ?? '')),
                            tags: @js(old('tags', $room->tags->pluck('name')->implode(', ') ?? ''))
                        },
                        normalizeTags(tags) {
                            if (!tags) return '';
                            return tags.split(',')
                                .map(t => t.trim().toLowerCase())
                                .filter(t => t)
                                .slice(0, 5)
                                .join(', ');
                        },
                        generateSlug(title) {
                            if (this.autoSlug) {
                                const slug = title.toLowerCase()
                                    .trim()
                                    .replace(/[^\w\s-]/g, '')
                                    .replace(/[\s_-]+/g, '-')
                                    .replace(/^-+|-+$/g, '');
                                document.querySelector('input[name=\'slug\']').value = slug;
                            }
                        },
                        handleCategoryChange(event) {
                            const select = event.target;
                            this.selectedCategories = Array.from(select.selectedOptions).map(opt => opt.value);
                            if (this.selectedCategories.length > 0 && !this.newSectionCategoryId) {
                                this.newSectionCategoryId = this.selectedCategories[0];
                            }
                        },
                        async createNewSection() {
                            if (!this.newSectionName || !this.newSectionCategoryId) {
                                alert('Please enter section name and select a category');
                                return;
                            }
                            this.loading = true;
                            try {
                                const response = await fetch('{{ route('admin.sections.quick-create') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        category_id: this.newSectionCategoryId,
                                        name: this.newSectionName,
                                        description: this.newSectionDescription,
                                        position: 0
                                    })
                                });
                                const data = await response.json();
                                if (data.success) {
                                    const sectionTitle = data.section.title || data.section.name || '';
                                    this.sections.push({
                                        id: data.section.id,
                                        name: sectionTitle,
                                        title: sectionTitle,
                                        category_id: data.section.category.id,
                                        category_name: data.section.category.name
                                    });
                                    const select = document.querySelector('select[name=\'sections[]\']');
                                    if (select) {
                                        const option = document.createElement('option');
                                        option.value = data.section.id;
                                        option.textContent = data.section.category.name + ' → ' + sectionTitle;
                                        option.selected = true;
                                        select.appendChild(option);
                                    }
                                    this.newSectionName = '';
                                    this.newSectionDescription = '';
                                    this.newSectionCategoryId = this.selectedCategories && this.selectedCategories.length > 0 ? this.selectedCategories[0] : '';
                                    this.showNewSectionForm = false;
                                } else {
                                    alert('Error creating section');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('Error creating section');
                            } finally {
                                this.loading = false;
                            }
                        },
                        init() {
                            // Sync previewData with form field values on load
                            this.$nextTick(() => {
                                const form = this.$el.closest('form') || document.querySelector('form');
                                if (form) {
                                    const titleInput = form.querySelector('[name="title"]');
                                    if (titleInput) this.previewData.title = titleInput.value || '';
                                    const summaryTextarea = form.querySelector('[name="summary"]');
                                    if (summaryTextarea) this.previewData.summary = summaryTextarea.value || '';
                                    const platformInput = form.querySelector('[name="platform"]');
                                    if (platformInput) this.previewData.platform = platformInput.value || '';
                                    const difficultySelect = form.querySelector('[name="difficulty"]');
                                    if (difficultySelect) this.previewData.difficulty = difficultySelect.value || '';
                                    const roomUrlInput = form.querySelector('[name="room_url"]');
                                    if (roomUrlInput) this.previewData.room_url = roomUrlInput.value || '';
                                    const objectiveGoalTextarea = form.querySelector('[name="objective_goal"]');
                                    if (objectiveGoalTextarea) this.previewData.objective_goal = objectiveGoalTextarea.value || '';
                                    const keyTechniquesTextarea = form.querySelector('[name="key_techniques_used"]');
                                    if (keyTechniquesTextarea) this.previewData.key_techniques_used = keyTechniquesTextarea.value || '';
                                    const toolsCommandsTextarea = form.querySelector('[name="tools_commands_used"]');
                                    if (toolsCommandsTextarea) this.previewData.tools_commands_used = toolsCommandsTextarea.value || '';
                                    const attackVectorTextarea = form.querySelector('[name="attack_vector_summary"]');
                                    if (attackVectorTextarea) this.previewData.attack_vector_summary = attackVectorTextarea.value || '';
                                    const flagEvidenceTextarea = form.querySelector('[name="flag_evidence_proof"]');
                                    if (flagEvidenceTextarea) this.previewData.flag_evidence_proof = flagEvidenceTextarea.value || '';
                                    const timeSpentInput = form.querySelector('[name="time_spent"]');
                                    if (timeSpentInput) this.previewData.time_spent = timeSpentInput.value || '';
                                    const reflectionTextarea = form.querySelector('[name="reflection_takeaways"]');
                                    if (reflectionTextarea) this.previewData.reflection_takeaways = reflectionTextarea.value || '';
                                    const difficultyConfirmationInput = form.querySelector('[name="difficulty_confirmation"]');
                                    if (difficultyConfirmationInput) this.previewData.difficulty_confirmation = difficultyConfirmationInput.value || '';
                                    const walkthroughTextarea = form.querySelector('[name="walkthrough_summary_steps"]');
                                    if (walkthroughTextarea) this.previewData.walkthrough_summary_steps = walkthroughTextarea.value || '';
                                    const toolsEnvironmentTextarea = form.querySelector('[name="tools_environment"]');
                                    if (toolsEnvironmentTextarea) this.previewData.tools_environment = toolsEnvironmentTextarea.value || '';
                                    const commandLogTextarea = form.querySelector('[name="command_log_snippet"]');
                                    if (commandLogTextarea) this.previewData.command_log_snippet = commandLogTextarea.value || '';
                                    const roomIdAuthorInput = form.querySelector('[name="room_id_author"]');
                                    if (roomIdAuthorInput) this.previewData.room_id_author = roomIdAuthorInput.value || '';
                                    const completionScreenshotInput = form.querySelector('[name="completion_screenshot_report_link"]');
                                    if (completionScreenshotInput) this.previewData.completion_screenshot_report_link = completionScreenshotInput.value || '';
                                    const platformUsernameInput = form.querySelector('[name="platform_username"]');
                                    if (platformUsernameInput) this.previewData.platform_username = platformUsernameInput.value || '';
                                    const platformProfileLinkInput = form.querySelector('[name="platform_profile_link"]');
                                    if (platformProfileLinkInput) this.previewData.platform_profile_link = platformProfileLinkInput.value || '';
                                    const statusSelect = form.querySelector('[name="status"]');
                                    if (statusSelect) this.previewData.status = statusSelect.value || 'in_progress';
                                    const scorePointsInput = form.querySelector('[name="score_points_earned"]');
                                    if (scorePointsInput) this.previewData.score_points_earned = scorePointsInput.value || '';
                                    const tagsInput = form.querySelector('[name="tags"]');
                                    if (tagsInput) this.previewData.tags = tagsInput.value || '';
                                }
                            });
                        }
                    }));
                } catch (e) {
                    console.error('Error registering Alpine component:', e);
                }
            }
        };
        
        if (window.Alpine && window.Alpine.data) {
            window.registerRoomEditComponent();
        } else {
            document.addEventListener('alpine:init', window.registerRoomEditComponent);
        }
    </script>

    {{-- Hero Header --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-red-600 via-orange-600 to-yellow-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Edit Room</h1>
                    <p class="text-red-100 text-lg">Update your CTF room completion details</p>
                </div>
                <div class="hidden md:block">
                    <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Two Column Layout: Form Left, Preview Right --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="roomEdit()">
            {{-- Form Column (2/3 width) --}}
            <div class="lg:col-span-2 space-y-6">
                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.rooms.update', $room) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Basic Information Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Basic Information
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <x-dual-language-input 
                                name="title" 
                                label="Title" 
                                :value="$room->getTranslations('title')"
                                placeholder="Enter room title"
                                required="true"
                            />

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Slug <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    name="slug" 
                                    value="{{ old('slug', $room->slug) }}"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none font-mono text-sm" 
                                    placeholder="url-friendly-slug"
                                    required />
                                <p class="text-xs text-gray-500 mt-1">URL-friendly identifier</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Summary <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="summary" 
                                    @input="previewData.summary = $event.target.value"
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none resize-none" 
                                    placeholder="Brief summary of what the room teaches..."
                                    required>{{ old('summary', $room->summary) }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Required field</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Description
                                </label>
                                <textarea 
                                    name="description" 
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none resize-none" 
                                    placeholder="Detailed description of the room...">{{ old('description', $room->description) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Platform
                                    </label>
                                    <input 
                                        name="platform" 
                                        value="{{ old('platform', $room->platform) }}"
                                        @input="previewData.platform = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                        placeholder="e.g., TryHackMe, HTB, PicoCTF" />
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Difficulty
                                    </label>
                                    <select 
                                        name="difficulty" 
                                        @change="previewData.difficulty = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                        <option value="">Select Difficulty</option>
                                        <option value="Easy" {{ old('difficulty', $room->difficulty) === 'Easy' ? 'selected' : '' }}>Easy</option>
                                        <option value="Medium" {{ old('difficulty', $room->difficulty) === 'Medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="Hard" {{ old('difficulty', $room->difficulty) === 'Hard' ? 'selected' : '' }}>Hard</option>
                                        <option value="Insane" {{ old('difficulty', $room->difficulty) === 'Insane' ? 'selected' : '' }}>Insane</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Room URL
                                    </label>
                                    <input 
                                        name="room_url" 
                                        type="url"
                                        value="{{ old('room_url', $room->room_url) }}"
                                        @input="previewData.room_url = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                        placeholder="https://tryhackme.com/room/..." />
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Completed At
                                    </label>
                                    <input 
                                        type="date" 
                                        name="completed_at" 
                                        value="{{ old('completed_at', optional($room->completed_at)->format('Y-m-d')) }}"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" />
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Status
                                    </label>
                                    <select 
                                        name="status" 
                                        @change="previewData.status = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none">
                                        <option value="in_progress" {{ old('status', $room->status ?? 'in_progress') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status', $room->status ?? 'in_progress') === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="retired" {{ old('status', $room->status ?? 'in_progress') === 'retired' ? 'selected' : '' }}>Retired</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Score / Points Earned
                                    </label>
                                    <input 
                                        type="number" 
                                        name="score_points_earned" 
                                        value="{{ old('score_points_earned', $room->score_points_earned) }}"
                                        @input="previewData.score_points_earned = $event.target.value"
                                        min="0" 
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all outline-none" 
                                        placeholder="e.g., 20" />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Learning & Purpose Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                Learning & Purpose
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Objective / Goal
                                </label>
                                <textarea 
                                    name="objective_goal" 
                                    @input="previewData.objective_goal = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="What you intended to learn (e.g., privilege escalation, SQLi)">{{ old('objective_goal', $room->objective_goal) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Key Techniques Used
                                </label>
                                <textarea 
                                    name="key_techniques_used" 
                                    @input="previewData.key_techniques_used = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="Concrete skills gained (e.g., Command injection, reverse shell via netcat, privilege escalation via cron job)">{{ old('key_techniques_used', $room->key_techniques_used) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tools / Commands Used
                                </label>
                                <textarea 
                                    name="tools_commands_used" 
                                    @input="previewData.tools_commands_used = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none font-mono text-sm" 
                                    placeholder="nmap, gobuster, linpeas, hydra, etc.">{{ old('tools_commands_used', $room->tools_commands_used) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Attack Vector Summary
                                </label>
                                <textarea 
                                    name="attack_vector_summary" 
                                    @input="previewData.attack_vector_summary = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="Your thought process (e.g., Enumeration → Exploit → PrivEsc)">{{ old('attack_vector_summary', $room->attack_vector_summary) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Flag Evidence / Proof
                                </label>
                                <textarea 
                                    name="flag_evidence_proof" 
                                    @input="previewData.flag_evidence_proof = $event.target.value"
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="user.txt and root.txt hashes or screenshots">{{ old('flag_evidence_proof', $room->flag_evidence_proof) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Time Spent (minutes)
                                    </label>
                                    <input 
                                        type="number" 
                                        name="time_spent" 
                                        value="{{ old('time_spent', $room->time_spent) }}"
                                        @input="previewData.time_spent = $event.target.value"
                                        min="0" 
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                        placeholder="e.g., 120" />
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Difficulty Confirmation
                                    </label>
                                    <input 
                                        name="difficulty_confirmation" 
                                        value="{{ old('difficulty_confirmation', $room->difficulty_confirmation) }}"
                                        @input="previewData.difficulty_confirmation = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" 
                                        placeholder="e.g., Official: Medium / My rating: Hard" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Reflection / Takeaways
                                </label>
                                <textarea 
                                    name="reflection_takeaways" 
                                    @input="previewData.reflection_takeaways = $event.target.value"
                                    rows="4" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" 
                                    placeholder="Why it mattered / what you'd do differently (e.g., Missed hidden directory enumeration step initially)">{{ old('reflection_takeaways', $room->reflection_takeaways) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Reproducibility Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M12 15h.01"/>
                                </svg>
                                Reproducibility
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Walkthrough Summary / Steps
                                </label>
                                <textarea 
                                    name="walkthrough_summary_steps" 
                                    @input="previewData.walkthrough_summary_steps = $event.target.value"
                                    rows="8" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none" 
                                    placeholder="Main steps in your attack chain">{{ old('walkthrough_summary_steps', $room->walkthrough_summary_steps) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tools & Environment
                                </label>
                                <textarea 
                                    name="tools_environment" 
                                    @input="previewData.tools_environment = $event.target.value"
                                    rows="3" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none" 
                                    placeholder="OS, VPN, browser extensions, notes software">{{ old('tools_environment', $room->tools_environment) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Command Log / Snippet
                                </label>
                                <textarea 
                                    name="command_log_snippet" 
                                    @input="previewData.command_log_snippet = $event.target.value"
                                    rows="10" 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none resize-none font-mono text-sm" 
                                    placeholder="Top 5-10 lines showing key commands">{{ old('command_log_snippet', $room->command_log_snippet) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Room ID / Author
                                    </label>
                                    <input 
                                        name="room_id_author" 
                                        value="{{ old('room_id_author', $room->room_id_author) }}"
                                        @input="previewData.room_id_author = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                        placeholder="Official name or link for reference" />
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Completion Screenshot / Report Link
                                    </label>
                                    <input 
                                        name="completion_screenshot_report_link" 
                                        type="url"
                                        value="{{ old('completion_screenshot_report_link', $room->completion_screenshot_report_link) }}"
                                        @input="previewData.completion_screenshot_report_link = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all outline-none" 
                                        placeholder="https://..." />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Traceability & Meta Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-.758l-1.102 1.102m0-5.656a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.102 1.101m-.758.758l1.102-1.102"/>
                                </svg>
                                Traceability & Meta
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Platform Username
                                    </label>
                                    <input 
                                        name="platform_username" 
                                        value="{{ old('platform_username', $room->platform_username) }}"
                                        @input="previewData.platform_username = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none" 
                                        placeholder="Your handle for validation" />
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Platform Profile Link
                                    </label>
                                    <input 
                                        name="platform_profile_link" 
                                        type="url"
                                        value="{{ old('platform_profile_link', $room->platform_profile_link) }}"
                                        @input="previewData.platform_profile_link = $event.target.value"
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none" 
                                        placeholder="https://tryhackme.com/p/..." />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Organization Section --}}
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zm3-7h.01M12 15h.01M16 15h.01"/>
                                </svg>
                                Organization
                            </h2>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Categories
                                </label>
                                <select 
                                    name="categories[]" 
                                    multiple 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none min-h-[150px]" 
                                    size="5" 
                                    @change="handleCategoryChange($event)">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $room->categories->contains($category->id) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple categories</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Sections (grouping within categories)
                                </label>
                                <select 
                                    name="sections[]" 
                                    multiple 
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none min-h-[150px]" 
                                    size="5">
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ $room->sections->contains($section->id) ? 'selected' : '' }}>
                                            {{ $section->category->name }} → {{ $section->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple sections.</p>
                                
                                <div class="mt-4">
                                    <button type="button" @click="showNewSectionForm = !showNewSectionForm" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors border border-blue-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add New Section
                                    </button>
                                </div>
                                
                                <div x-show="showNewSectionForm" x-cloak x-transition class="mt-4 p-6 bg-gradient-to-br from-teal-50 to-cyan-50 rounded-xl border-2 border-teal-200">
                                    <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Create New Section
                                    </h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                                            <select name="new_section_category_id" x-model="newSectionCategoryId" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" required>
                                                <option value="">Select a category...</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Section Name</label>
                                            <input type="text" x-model="newSectionName" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none" placeholder="e.g., Lesson 1">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Description (optional)</label>
                                            <textarea x-model="newSectionDescription" rows="3" class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-teal-500 focus:ring-2 focus:ring-teal-200 transition-all outline-none resize-none" placeholder="Brief description..."></textarea>
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="button" @click="createNewSection()" :disabled="loading" class="px-6 py-2.5 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-lg font-medium disabled:opacity-50 transition-all shadow-md hover:shadow-lg">
                                                <span x-show="!loading">Create & Add</span>
                                                <span x-show="loading" class="flex items-center gap-2">
                                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Saving...
                                                </span>
                                            </button>
                                            <button type="button" @click="showNewSectionForm = false; newSectionName = ''; newSectionDescription = ''; newSectionCategoryId = selectedCategories && selectedCategories.length > 0 ? selectedCategories[0] : '';" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tags (comma separated)
                                </label>
                                <input 
                                    name="tags" 
                                    value="{{ old('tags', $room->tags->pluck('name')->implode(', ')) }}"
                                    @input="previewData.tags = normalizeTags($event.target.value)"
                                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all outline-none" 
                                    placeholder="e.g., web, priv-esc, linux, ctf, recon" />
                                <p class="text-xs text-gray-500 mt-1">Max 5 tags. Tags will be lowercased and trimmed.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <a href="{{ route('admin.rooms.index') }}" class="px-6 py-3 text-lg font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-md">
                            Cancel
                        </a>
                        <button type="submit" class="px-8 py-3 text-lg font-semibold bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white rounded-lg transition-all shadow-md hover:shadow-lg">
                            Update Room
                        </button>
                    </div>
                </form>
            </div>

            {{-- Live Preview Column (1/3 width) --}}
            <div class="lg:col-span-1">
                <div class="sticky top-6 space-y-6">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Live Preview
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <h4 class="text-lg font-bold text-gray-900 mb-4">Room Card</h4>
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all cursor-pointer bg-white">
                                <div class="flex flex-wrap gap-2 mb-2" x-show="previewData && (previewData.status || previewData.difficulty || previewData.time_spent || previewData.score_points_earned)">
                                    <template x-if="previewData && previewData.status === 'completed'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            ✓ Completed
                                        </span>
                                    </template>
                                    <template x-if="previewData && previewData.status === 'in_progress'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            ⏳ In Progress
                                        </span>
                                    </template>
                                    <template x-if="previewData && previewData.status === 'retired'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                            Retired
                                        </span>
                                    </template>
                                    <template x-if="previewData && previewData.difficulty">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-800': previewData.difficulty === 'Easy',
                                                'bg-yellow-100 text-yellow-800': previewData.difficulty === 'Medium',
                                                'bg-orange-100 text-orange-800': previewData.difficulty === 'Hard',
                                                'bg-red-100 text-red-800': previewData.difficulty === 'Insane'
                                            }"
                                            x-text="previewData.difficulty">
                                        </span>
                                    </template>
                                    <template x-if="previewData && previewData.time_spent">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                            ⏱ <span x-text="previewData.time_spent"></span> min
                                        </span>
                                    </template>
                                    <template x-if="previewData && previewData.score_points_earned">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            🏆 <span x-text="previewData.score_points_earned"></span> pts
                                        </span>
                                    </template>
                                </div>
                                <h3 class="font-semibold text-gray-900 mb-2" x-text="(previewData && previewData.title) ? previewData.title : 'Untitled Room'"></h3>
                                <p class="text-sm text-gray-600 line-clamp-2 mb-3" x-show="previewData && previewData.summary" x-text="(previewData && previewData.summary) ? previewData.summary : ''"></p>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <span x-show="previewData && previewData.platform" x-text="(previewData && previewData.platform) ? previewData.platform : ''"></span>
                                </div>
                                <div class="flex flex-wrap gap-1 mt-2" x-show="previewData && previewData.tags">
                                    <template x-for="tag in ((previewData && previewData.tags ? previewData.tags.split(',').map(t => t.trim()).filter(t => t) : []) || [])" :key="tag">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <span x-text="tag"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 space-y-4">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Detail View Snippets</h4>
                        
                        <template x-if="previewData && previewData.objective_goal">
                            <div class="p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                                <p class="text-xs font-semibold text-purple-700 uppercase tracking-wide mb-1">Objective / Goal</p>
                                <p class="text-sm text-gray-800" x-text="(previewData && previewData.objective_goal) ? previewData.objective_goal : ''"></p>
                            </div>
                        </template>

                        <template x-if="previewData && previewData.key_techniques_used">
                            <div class="p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                                <p class="text-xs font-semibold text-purple-700 uppercase tracking-wide mb-1">Key Techniques</p>
                                <p class="text-sm text-gray-800" x-text="(previewData && previewData.key_techniques_used) ? previewData.key_techniques_used : ''"></p>
                            </div>
                        </template>

                        <template x-if="previewData && previewData.attack_vector_summary">
                            <div class="p-4 bg-orange-50 rounded-lg border-l-4 border-orange-500">
                                <p class="text-xs font-semibold text-orange-700 uppercase tracking-wide mb-1">Attack Vector</p>
                                <p class="text-sm text-gray-800" x-text="(previewData && previewData.attack_vector_summary) ? previewData.attack_vector_summary : ''"></p>
                            </div>
                        </template>

                        <template x-if="previewData && previewData.command_log_snippet">
                            <div class="p-4 bg-orange-50 rounded-lg border-l-4 border-orange-500">
                                <p class="text-xs font-semibold text-orange-700 uppercase tracking-wide mb-1">Command Log</p>
                                <pre class="text-sm text-gray-800 whitespace-pre-wrap font-mono" x-text="(previewData && previewData.command_log_snippet) ? previewData.command_log_snippet : ''"></pre>
                            </div>
                        </template>

                        <template x-if="previewData && previewData.reflection_takeaways">
                            <div class="p-4 bg-teal-50 rounded-lg border-l-4 border-teal-500">
                                <p class="text-xs font-semibold text-teal-700 uppercase tracking-wide mb-1">Reflection</p>
                                <p class="text-sm text-gray-800" x-text="(previewData && previewData.reflection_takeaways) ? previewData.reflection_takeaways : ''"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

