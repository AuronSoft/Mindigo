@php
    $editing = isset($exam);
    $config = old('generation_config', $exam->generation_config ?? []);
    $counts = old('counts', $config['counts'] ?? ['single_choice' => 20, 'multiple_choice' => 0, 'true_false' => 0, 'short_answer' => 0]);
    $points = old('points', $config['points'] ?? ['single_choice' => 1, 'multiple_choice' => 1, 'true_false' => 1, 'short_answer' => 1]);
    $totalRequested = collect($types)->sum(fn ($type) => (int) ($counts[$type] ?? 0));
    $selectedSubject = old('subject', $exam->subject ?? '');
    $selectedGenerationSubject = old('generation_subject', $config['subject'] ?? '');
    $selectedClassroomIds = collect(old('classroom_ids', $exam->audience['classrooms'] ?? []))->map(fn ($id) => (int) $id);
@endphp

<script type="application/json" data-exam-subject-topics>@json($subjectTopics ?? [])</script>

<div class="exam-studio" data-exam-topic-builder data-exam-wizard data-step-error="@lang('Mindigo-exam-management::app.wizard_complete_step')" data-count-error="@lang('Mindigo-exam-management::app.messages.no_generation_count')">
    <div class="exam-wizard-header">
        <div><p class="exam-studio-label">@lang('Mindigo-exam-management::app.authoring_studio')</p><h2>@lang('Mindigo-exam-management::app.authoring_title')</h2><p>@lang('Mindigo-exam-management::app.authoring_description')</p></div>
        <div class="exam-wizard-progress"><span data-exam-wizard-progress style="width: 25%"></span></div>
    </div>
    <div class="exam-studio-layout">
        <aside class="exam-studio-sidebar">
            <div>
                <p class="exam-studio-label">@lang('Mindigo-exam-management::app.section_list_label')</p>
                <div class="exam-part-list">
                    <a href="#exam-part-source" class="exam-part-item is-active" data-exam-part-link="source">@lang('Mindigo-exam-management::app.part_generation')</a>
                    <a href="#exam-part-runtime" class="exam-part-item" data-exam-part-link="runtime">@lang('Mindigo-exam-management::app.part_runtime')</a>
                    <a href="#exam-part-info" class="exam-part-item" data-exam-part-link="info">@lang('Mindigo-exam-management::app.part_info')</a>
                    <a href="#exam-part-review" class="exam-part-item" data-exam-part-link="review">@lang('Mindigo-exam-management::app.part_review')</a>
                </div>
            </div>

            <div>
                <p class="exam-studio-label">@lang('Mindigo-exam-management::app.question_index_label')</p>
                <div class="exam-question-index" data-exam-question-index></div>
            </div>

            <div class="exam-progress-card">
                <div class="flex items-center justify-between">
                    <strong>@lang('Mindigo-exam-management::app.generation')</strong>
                    <span data-exam-total-count>{{ $totalRequested }}</span>
                </div>
                <div class="exam-progress-track"><span data-exam-progress style="width: {{ min(100, max(12, $totalRequested * 3)) }}%"></span></div>
                <p>@lang('Mindigo-exam-management::app.total_questions')</p>
            </div>
        </aside>

        <section class="exam-studio-main">
            <p class="exam-studio-label">@lang('Mindigo-exam-management::app.question_list_label')</p>

            <article class="exam-builder-card exam-panel-card is-highlight" id="exam-part-source" data-exam-part="source">
                <div class="exam-section-head"><span>01</span><div><h2>@lang('Mindigo-exam-management::app.generation')</h2><p>@lang('Mindigo-exam-management::app.generation_desc')</p></div></div>
                <div class="exam-source-tools mt-5">
                    <button type="button" class="exam-source-tool is-active" data-exam-source-mode="bank"><span class="bg-green-50 text-green-700"><x-heroicon-o-circle-stack class="h-5 w-5" /></span><strong>@lang('Mindigo-exam-management::app.source_bank')</strong><small>@lang('Mindigo-exam-management::app.source_bank_desc')</small></button>
                    @if(Route::has('teacher.questions.import'))<a href="{{ route('teacher.questions.import') }}" class="exam-source-tool"><span class="bg-sky-50 text-sky-700"><x-heroicon-o-arrow-up-tray class="h-5 w-5" /></span><strong>@lang('Mindigo-exam-management::app.source_import')</strong><small>@lang('Mindigo-exam-management::app.source_import_desc')</small></a>@endif
                    @if(Route::has('teacher.questions.create'))<a href="{{ route('teacher.questions.create') }}" class="exam-source-tool"><span class="bg-violet-50 text-violet-700"><x-heroicon-o-pencil-square class="h-5 w-5" /></span><strong>@lang('Mindigo-exam-management::app.source_manual')</strong><small>@lang('Mindigo-exam-management::app.source_manual_desc')</small></a>@endif
                </div>
                <div class="exam-form-grid mt-5">
                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.folder')</span><select name="folder_id" class="exam-select"><option value="">@lang('Mindigo-exam-management::app.any_folder')</option>@foreach($folders as $folder)<option value="{{ $folder->id }}" @selected((string) old('folder_id', $config['folder_id'] ?? '') === (string) $folder->id)>{{ $folder->name }} ({{ $folder->questions_count }})</option>@endforeach</select></label>

                    <label class="exam-field">
                        <span>@lang('Mindigo-exam-management::app.generation_subject')</span>
                        <input type="hidden" name="generation_subject" value="{{ $selectedGenerationSubject }}" data-exam-subject-select data-topic-target="generation_topic" data-exam-subject-value>
                        <div class="relative" data-exam-subject-picker>
                            <button type="button" class="exam-select flex items-center justify-between pr-3" data-exam-subject-button>
                                <span data-exam-subject-label class="{{ $selectedGenerationSubject ? 'text-slate-800' : 'text-slate-400' }}">{{ $selectedGenerationSubject ?: __('Mindigo-exam-management::app.any_subject') }}</span>
                                <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 fill-none stroke-current stroke-[2.5] text-slate-400"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="exam-subject-panel hidden" data-exam-subject-panel>
                                <div class="border-b border-slate-100 p-2">
                                    <input type="text" class="exam-subject-search" placeholder="@lang('Mindigo-exam-management::app.any_subject')" data-exam-subject-search>
                                </div>
                                <ul class="max-h-56 overflow-y-auto p-1" data-exam-subject-list>
                                    <li class="exam-subject-option" data-value="">@lang('Mindigo-exam-management::app.any_subject')</li>
                                    @foreach($subjects as $subject)
                                        <li class="exam-subject-option {{ $selectedGenerationSubject === $subject ? 'is-selected' : '' }}" data-value="{{ $subject }}">{{ $subject }}</li>
                                    @endforeach
                                    <li class="exam-subject-empty hidden">@lang('Mindigo-exam-management::app.empty_title')</li>
                                </ul>
                            </div>
                        </div>
                    </label>

                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.generation_topic')</span><select name="generation_topic" class="exam-select" data-exam-topic-select data-topic-name="generation_topic" data-current-value="{{ old('generation_topic', $config['topic'] ?? '') }}" data-placeholder="@lang('Mindigo-exam-management::app.any_topic')"><option value="">@lang('Mindigo-exam-management::app.any_topic')</option></select></label>
                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.generation_difficulty')</span><select name="generation_difficulty" class="exam-select"><option value="">@lang('Mindigo-exam-management::app.any_difficulty')</option>@foreach($difficulties as $difficulty)<option value="{{ $difficulty }}" @selected(old('generation_difficulty', $config['difficulty'] ?? '') === $difficulty)>@lang('Mindigo-exam-management::app.difficulties.' . $difficulty)</option>@endforeach</select></label>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3"><h3 class="text-sm font-black text-slate-800">@lang('Mindigo-exam-management::app.question_structure') <span class="exam-required" aria-hidden="true">*</span></h3><span class="text-xs font-semibold text-slate-400">@lang('Mindigo-exam-management::app.required_hint')</span></div>
                <div class="exam-type-grid mt-3">
                    @foreach($types as $type)
                        <article class="exam-type-card" id="exam-type-{{ $type }}" data-exam-question-target>
                            <div class="flex items-center justify-between gap-2">
                                <strong>@lang('Mindigo-exam-management::app.question_types.' . $type)</strong>
                                <span class="exam-answer-pill">@lang('Mindigo-exam-management::app.has_answer')</span>
                            </div>
                            <label><span>@lang('Mindigo-exam-management::app.count')</span><input type="number" min="0" max="200" name="counts[{{ $type }}]" value="{{ $counts[$type] ?? 0 }}" class="exam-input" data-exam-count-input data-index-target="exam-type-{{ $type }}"></label>
                            <label><span>@lang('Mindigo-exam-management::app.points')</span><input type="number" min="0" max="100" step="0.25" name="points[{{ $type }}]" value="{{ $points[$type] ?? 1 }}" class="exam-input"></label>
                        </article>
                    @endforeach
                </div>

                @if($editing)
                    <label class="exam-toggle mt-5"><input type="checkbox" name="regenerate_questions" value="1"><span></span><strong>@lang('Mindigo-exam-management::app.regenerate_questions')</strong></label>
                @endif
            </article>

            <article class="exam-builder-card exam-panel-card" id="exam-part-runtime" data-exam-part="runtime">
                <div class="exam-section-head"><span>02</span><div><h2>@lang('Mindigo-exam-management::app.runtime')</h2><p>@lang('Mindigo-exam-management::app.runtime_desc')</p></div></div>
                <div class="exam-form-grid mt-5">
                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.duration_minutes') <span class="exam-required" aria-hidden="true">*</span></span><input type="number" min="1" max="600" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?? 45) }}" class="exam-input" required></label>
                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.max_attempts') <span class="exam-required" aria-hidden="true">*</span></span><input type="number" min="1" max="20" name="max_attempts" value="{{ old('max_attempts', $exam->max_attempts ?? 1) }}" class="exam-input" required></label>
                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.passing_score') <span class="exam-required" aria-hidden="true">*</span></span><input type="number" min="0" step="0.25" name="passing_score" value="{{ old('passing_score', $exam->passing_score ?? 0) }}" class="exam-input" required></label>
                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.starts_at')</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($exam) && $exam->starts_at ? $exam->starts_at->format('Y-m-d\TH:i') : '') }}" class="exam-input"></label>
                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.ends_at')</span><input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($exam) && $exam->ends_at ? $exam->ends_at->format('Y-m-d\TH:i') : '') }}" class="exam-input"></label>
                    <div class="exam-toggle-grid md:col-span-2">
                        @foreach(['shuffle_questions', 'shuffle_answers', 'show_results'] as $toggle)
                            <label class="exam-toggle"><input type="checkbox" name="{{ $toggle }}" value="1" @checked(old($toggle, $exam->{$toggle} ?? true))><span></span><strong>@lang('Mindigo-exam-management::app.' . $toggle)</strong></label>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="exam-builder-card exam-panel-card" id="exam-part-info" data-exam-part="info">
                <div class="exam-section-head"><span>03</span><div><h2>@lang('Mindigo-exam-management::app.basic_info')</h2><p>@lang('Mindigo-exam-management::app.basic_info_desc')</p></div></div>
                <div class="exam-form-grid mt-5">
                    <fieldset class="exam-field md:col-span-2">
                        <legend>@lang('Mindigo-exam-management::app.assigned_classrooms') <span class="exam-required" aria-hidden="true">*</span></legend>
                        <p class="mt-1 text-xs font-semibold text-slate-400">@lang('Mindigo-exam-management::app.assigned_classrooms_help')</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($classrooms ?? [] as $classroom)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 hover:border-green-300">
                                    <input type="checkbox" name="classroom_ids[]" value="{{ $classroom->id }}" class="h-4 w-4 accent-green-600" @checked($selectedClassroomIds->contains($classroom->id))>
                                    <span class="min-w-0"><strong class="block truncate text-sm text-slate-800">{{ $classroom->name }}</strong><small class="text-xs font-semibold text-slate-400">{{ $classroom->students_count }} @lang('Mindigo-exam-management::app.students_unit')</small></span>
                                </label>
                            @endforeach
                        </div>
                        @error('classroom_ids')<span class="mt-2 block text-xs font-bold text-red-600">{{ $message }}</span>@enderror
                    </fieldset>
                    <label class="exam-field md:col-span-2"><span>@lang('Mindigo-exam-management::app.title_field') <span class="exam-required" aria-hidden="true">*</span></span><input name="title" value="{{ old('title', $exam->title ?? '') }}" class="exam-input" required></label>

                    <label class="exam-field">
                        <span>@lang('Mindigo-exam-management::app.subject')</span>
                        <input type="hidden" name="subject" value="{{ $selectedSubject }}" data-exam-subject-select data-topic-target="topic" data-exam-subject-value>
                        <div class="relative" data-exam-subject-picker>
                            <button type="button" class="exam-select flex items-center justify-between pr-3" data-exam-subject-button>
                                <span data-exam-subject-label class="{{ $selectedSubject ? 'text-slate-800' : 'text-slate-400' }}">{{ $selectedSubject ?: __('Mindigo-exam-management::app.select_subject') }}</span>
                                <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 fill-none stroke-current stroke-[2.5] text-slate-400"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="exam-subject-panel hidden" data-exam-subject-panel>
                                <div class="border-b border-slate-100 p-2">
                                    <input type="text" class="exam-subject-search" placeholder="@lang('Mindigo-exam-management::app.select_subject')" data-exam-subject-search>
                                </div>
                                <ul class="max-h-56 overflow-y-auto p-1" data-exam-subject-list>
                                    @foreach($subjects as $subject)
                                        <li class="exam-subject-option {{ $selectedSubject === $subject ? 'is-selected' : '' }}" data-value="{{ $subject }}">{{ $subject }}</li>
                                    @endforeach
                                    <li class="exam-subject-empty hidden">@lang('Mindigo-exam-management::app.empty_title')</li>
                                </ul>
                            </div>
                        </div>
                    </label>

                    <label class="exam-field"><span>@lang('Mindigo-exam-management::app.topic')</span><select name="topic" class="exam-select" data-exam-topic-select data-topic-name="topic" data-current-value="{{ old('topic', $exam->topic ?? '') }}" data-placeholder="@lang('Mindigo-exam-management::app.select_topic')"><option value="">@lang('Mindigo-exam-management::app.select_topic')</option></select></label>
                    <label class="exam-field md:col-span-2"><span>@lang('Mindigo-exam-management::app.description')</span><textarea name="description" class="exam-textarea">{{ old('description', $exam->description ?? '') }}</textarea></label>
                </div>
            </article>

            <article class="exam-builder-card exam-panel-card" id="exam-part-review" data-exam-part="review">
                <div class="exam-section-head"><span>04</span><div><h2>@lang('Mindigo-exam-management::app.review_before_save')</h2><p>@lang('Mindigo-exam-management::app.review_before_save_desc')</p></div></div>
                <div class="exam-review-grid mt-5">
                    <div><span>@lang('Mindigo-exam-management::app.title_field')</span><strong data-exam-review-title>—</strong></div>
                    <div><span>@lang('Mindigo-exam-management::app.total_questions')</span><strong data-exam-review-questions>{{ $totalRequested }}</strong></div>
                    <div><span>@lang('Mindigo-exam-management::app.duration_minutes')</span><strong data-exam-review-duration>45</strong></div>
                    <div><span>@lang('Mindigo-exam-management::app.assigned_classrooms')</span><strong data-exam-review-classrooms>0</strong></div>
                </div>
                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-800"><strong class="block font-black">@lang('Mindigo-exam-management::app.review_note_title')</strong>@lang('Mindigo-exam-management::app.review_note')</div>
            </article>
        </section>
    </div>

    <div class="exam-studio-footer" aria-label="@lang('Mindigo-exam-management::app.actions')">
        <button type="button" class="exam-button exam-button-secondary hidden" data-exam-wizard-previous><x-heroicon-o-arrow-left class="h-4 w-4" />@lang('Mindigo-exam-management::app.previous_step')</button>
        <div class="flex-1"></div>
        <button type="button" class="exam-button exam-button-primary" data-exam-wizard-next>@lang('Mindigo-exam-management::app.next_step')<x-heroicon-o-arrow-right class="h-4 w-4" /></button>
        <div class="exam-studio-submit-actions hidden">
            <button type="submit" class="exam-save-chip">
                <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                @lang('Mindigo-exam-management::app.save')
            </button>
            <button type="submit" name="submit_for_review" value="1" class="exam-review-button">
                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                @lang('Mindigo-exam-management::app.review_result')
            </button>
        </div>
    </div>
</div>
