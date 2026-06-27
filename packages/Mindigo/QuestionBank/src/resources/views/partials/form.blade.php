@php
    $editing = isset($question);
    $selectedType = old('type', $question->type ?? 'single_choice');
    $options = old('options', $question->options ?? ['', '', '', '']);
    $options = array_values(array_pad(array_filter((array) $options, fn ($value) => $value !== null), 4, ''));
    $correctAnswers = old('correct_answers', $question->correct_answers ?? []);
    $singleAnswer = old('correct_answer_single', $correctAnswers[0] ?? '');
    $shortAnswer = old('short_answer_text', implode(PHP_EOL, $selectedType === 'short_answer' && isset($question) ? ($question->correct_answers ?? []) : []));
@endphp

<div class="question-builder" data-question-builder>
    <script type="application/json" data-question-subject-topics>@json($subjectTopics ?? [])</script>

    <div class="question-studio-toolbar">
        <button type="button" class="question-upload-button">
            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 8 5-5 5 5"/><path d="M5 19h14"/></svg>
            @lang('Mindigo-question-bank::app.upload_document')
        </button>
        <button type="submit" name="submit_for_review" value="1" class="question-review-button">
            <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            @lang('Mindigo-question-bank::app.review_result')
        </button>
    </div>

    <div class="question-studio-actions">
        <button type="button" class="question-return-button" onclick="history.back()">
            <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            @lang('Mindigo-question-bank::app.back')
        </button>
        <button type="submit" class="question-save-chip">
            <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            @lang('Mindigo-question-bank::app.save')
        </button>
    </div>

    <div class="question-studio-layout">
        <aside class="question-studio-sidebar">
            <div>
                <p class="question-studio-label">@lang('Mindigo-question-bank::app.section_list_label')</p>
                <div class="question-part-list">
                    <a href="#question-part-content" class="question-part-item is-active">@lang('Mindigo-question-bank::app.part_content')</a>
                    <a href="#question-part-answers" class="question-part-item">@lang('Mindigo-question-bank::app.part_answers')</a>
                    <a href="#question-part-review" class="question-part-item">@lang('Mindigo-question-bank::app.part_review')</a>
                </div>
            </div>

            <div>
                <p class="question-studio-label">@lang('Mindigo-question-bank::app.question_index_label')</p>
                <div class="question-mini-index">
                    @foreach(range(1, 10) as $number)
                        <span class="{{ $number === 1 ? 'is-filled' : '' }}">{{ $number }}</span>
                    @endforeach
                </div>
            </div>

            <div class="question-progress-card">
                <div class="flex items-center justify-between">
                    <strong>@lang('Mindigo-question-bank::app.progress')</strong>
                    <span>30%</span>
                </div>
                <div class="question-progress-track"><span></span></div>
                <p>@lang('Mindigo-question-bank::app.progress_done')</p>
            </div>
        </aside>

        <section class="question-studio-main">
            <p class="question-studio-label">@lang('Mindigo-question-bank::app.question_list_label')</p>

    <section class="question-builder-card is-highlight" id="question-part-content">
        <div class="question-section-head">
            <span>01</span>
            <div>
                <h2>@lang('Mindigo-question-bank::app.section_content')</h2>
                <p>@lang('Mindigo-question-bank::app.section_content_desc')</p>
            </div>
        </div>

        <div class="question-form-grid mt-5">
            {{-- Thanh dropdow  --}}
            <label class="question-field">
    <span>@lang('Mindigo-question-bank::app.subject')</span>

    {{-- Hidden input thật để submit --}}
    <input type="hidden" name="subject" id="qb-subject-value"
       value="{{ old('subject', $question->subject ?? '') }}" required
       data-question-subject-select
       data-topic-target="topic">

    {{-- Trigger --}}
    <div class="relative" id="qb-subject-wrapper">
        <button type="button" id="qb-subject-btn" class="question-select flex w-full items-center justify-between pr-3">
            <span id="qb-subject-label"
                  class="{{ old('subject', $question->subject ?? '') ? 'text-slate-800' : 'text-slate-400' }}">
                {{ old('subject', $question->subject ?? '') ?: __('Mindigo-question-bank::app.select_subject') }}
            </span>
            <svg viewBox="0 0 24 24" class="h-4 w-4 shrink-0 fill-none stroke-current stroke-[2.5] text-slate-400"><path d="m6 9 6 6 6-6"/></svg>
        </button>

        {{-- Panel --}}
        <div id="qb-subject-panel"
             class="absolute z-50 mt-1 hidden w-full rounded-2xl border border-slate-200 bg-white shadow-lg">

            {{-- Search --}}
            <div class="border-b border-slate-100 p-2">
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 focus-within:border-green-400 focus-within:bg-white">
                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 shrink-0 fill-none stroke-current stroke-[2.5] text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="qb-subject-search"
                           placeholder="Tìm môn học..."
                           class="w-full bg-transparent text-sm font-bold text-slate-700 outline-none placeholder:text-slate-400">
                </div>
            </div>

            {{-- List --}}
            <ul id="qb-subject-list" class="max-h-48 overflow-y-auto p-1">
                @foreach($subjects as $subject)
                    <li class="qb-subject-option cursor-pointer rounded-xl px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-green-50 hover:text-green-700
                               {{ old('subject', $question->subject ?? '') === $subject ? 'bg-green-50 text-green-700' : '' }}"
                        data-value="{{ $subject }}">
                        {{ $subject }}
                    </li>
                @endforeach
                <li id="qb-subject-empty" class="hidden px-3 py-2 text-sm font-semibold text-slate-400">
                    Không tìm thấy môn học
                </li>
            </ul>

            {{-- Thêm mới --}}
            <div class="border-t border-slate-100 p-2">
                <div class="flex gap-2">
                    <input type="text" id="qb-subject-new"
                           placeholder="Nhập tên môn học mới..."
                           class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-slate-700 outline-none focus:border-green-400 focus:bg-white">
                    <button type="button" id="qb-subject-add"
                            class="inline-flex shrink-0 items-center gap-1 rounded-xl bg-green-600 px-3 py-2 text-xs font-black text-white transition hover:bg-green-500">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-none stroke-current stroke-[2.5]"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Thêm
                    </button>
                </div>
            </div>
        </div>
    </div>

    @error('subject')<strong>{{ $message }}</strong>@enderror
</label>

            <label class="question-field">
                <span>@lang('Mindigo-question-bank::app.topic')</span>
                <select name="topic" class="question-select" data-question-topic-select data-topic-name="topic" data-current-value="{{ old('topic', $question->topic ?? '') }}" data-placeholder="@lang('Mindigo-question-bank::app.select_topic')">
                    <option value="">@lang('Mindigo-question-bank::app.select_topic')</option>
                </select>
                @error('topic')<strong>{{ $message }}</strong>@enderror
            </label>

            <label class="question-field">
                <span>@lang('Mindigo-question-bank::app.folder')</span>
                <select name="folder_id" class="question-select">
                    <option value="">@lang('Mindigo-question-bank::app.no_folder')</option>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" @selected((string) old('folder_id', $question->folder_id ?? '') === (string) $folder->id)>{{ $folder->name }}</option>
                    @endforeach
                </select>
                @error('folder_id')<strong>{{ $message }}</strong>@enderror
            </label>

            <label class="question-field">
                <span>@lang('Mindigo-question-bank::app.type')</span>
                <select name="type" class="question-select" required data-question-type>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected($selectedType === $type)>@lang('Mindigo-question-bank::app.types.' . $type)</option>
                    @endforeach
                </select>
                @error('type')<strong>{{ $message }}</strong>@enderror
            </label>

            <label class="question-field">
                <span>@lang('Mindigo-question-bank::app.difficulty')</span>
                <select name="difficulty" class="question-select" required>
                    @foreach($difficulties as $difficulty)
                        <option value="{{ $difficulty }}" @selected(old('difficulty', $question->difficulty ?? 'medium') === $difficulty)>@lang('Mindigo-question-bank::app.difficulties.' . $difficulty)</option>
                    @endforeach
                </select>
                @error('difficulty')<strong>{{ $message }}</strong>@enderror
            </label>

            <label class="question-field md:col-span-2">
                <span>@lang('Mindigo-question-bank::app.content')</span>
                <textarea name="content" class="question-stem" required>{{ old('content', $question->content ?? '') }}</textarea>
                @error('content')<strong>{{ $message }}</strong>@enderror
            </label>
        </div>
    </section>

    <section class="question-builder-card" data-option-panel id="question-part-answers">
        <div class="question-section-head">
            <span>02</span>
            <div>
                <h2>@lang('Mindigo-question-bank::app.section_answers')</h2>
                <p>@lang('Mindigo-question-bank::app.section_answers_desc')</p>
            </div>
        </div>

        <div class="mt-5 space-y-3" data-option-list>
            @foreach($options as $index => $option)
                <div class="question-option-row" data-option-row>
                    <label class="question-answer-pick" title="@lang('Mindigo-question-bank::app.mark_correct')">
                        <input
                            type="radio"
                            name="correct_answer_single"
                            value="{{ $option }}"
                            @checked($singleAnswer !== '' && $singleAnswer === $option)
                            data-single-answer
                        >
                        <input
                            type="checkbox"
                            name="correct_answers[]"
                            value="{{ $option }}"
                            @checked(in_array($option, (array) $correctAnswers, true))
                            data-multiple-answer
                        >
                        <span></span>
                    </label>
                    <input name="options[]" value="{{ $option }}" class="question-option-input" placeholder="@lang('Mindigo-question-bank::app.option_placeholder', ['index' => $index + 1])" data-option-input>
                    <button type="button" class="question-icon-button" data-remove-option aria-label="@lang('Mindigo-question-bank::app.remove_option')">
                        <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            @endforeach
        </div>

        <button type="button" class="question-add-option" data-add-option>
            <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            @lang('Mindigo-question-bank::app.add_option')
        </button>

        @error('options')<strong class="question-error">{{ $message }}</strong>@enderror
        @error('correct_answers')<strong class="question-error">{{ $message }}</strong>@enderror
    </section>

    <section class="question-builder-card hidden" data-short-answer-panel>
        <div class="question-section-head">
            <span>02</span>
            <div>
                <h2>@lang('Mindigo-question-bank::app.section_short_answer')</h2>
                <p>@lang('Mindigo-question-bank::app.section_short_answer_desc')</p>
            </div>
        </div>
        <label class="question-field mt-5">
            <span>@lang('Mindigo-question-bank::app.correct_answers')</span>
            <textarea name="short_answer_text" class="question-textarea" placeholder="@lang('Mindigo-question-bank::app.answers_hint')">{{ $shortAnswer }}</textarea>
            @error('short_answer_text')<strong>{{ $message }}</strong>@enderror
        </label>
    </section>

    <section class="question-builder-card hidden" data-essay-panel>
        <div class="question-section-head">
            <span>02</span>
            <div>
                <h2>@lang('Mindigo-question-bank::app.section_essay')</h2>
                <p>@lang('Mindigo-question-bank::app.section_essay_desc')</p>
            </div>
        </div>
    </section>

    <section class="question-builder-card" id="question-part-review">
        <div class="question-section-head">
            <span>03</span>
            <div>
                <h2>@lang('Mindigo-question-bank::app.section_review')</h2>
                <p>@lang('Mindigo-question-bank::app.section_review_desc')</p>
            </div>
        </div>

        <div class="question-form-grid mt-5">
            <label class="question-field md:col-span-2">
                <span>@lang('Mindigo-question-bank::app.explanation')</span>
                <textarea name="explanation" class="question-textarea">{{ old('explanation', $question->explanation ?? '') }}</textarea>
                @error('explanation')<strong>{{ $message }}</strong>@enderror
            </label>

            <label class="question-field md:col-span-2">
                <span>@lang('Mindigo-question-bank::app.tags')</span>
                <input name="tags_text" value="{{ old('tags_text', isset($question) ? implode(', ', $question->tags ?? []) : '') }}" class="question-input" placeholder="@lang('Mindigo-question-bank::app.tags_hint')">
                @error('tags_text')<strong>{{ $message }}</strong>@enderror
            </label>
        </div>
    </section>

        </section>
    </div>

    <template data-option-template>
        <div class="question-option-row" data-option-row>
            <label class="question-answer-pick" title="@lang('Mindigo-question-bank::app.mark_correct')">
                <input type="radio" name="correct_answer_single" value="" data-single-answer>
                <input type="checkbox" name="correct_answers[]" value="" data-multiple-answer>
                <span></span>
            </label>
            <input name="options[]" value="" class="question-option-input" placeholder="@lang('Mindigo-question-bank::app.option_placeholder', ['index' => '__INDEX__'])" data-option-input>
            <button type="button" class="question-icon-button" data-remove-option aria-label="@lang('Mindigo-question-bank::app.remove_option')">
                <svg viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current stroke-[2.5]" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </template>
</div>

<script>
(function () {
    const btn     = document.getElementById('qb-subject-btn');
    const panel   = document.getElementById('qb-subject-panel');
    const search  = document.getElementById('qb-subject-search');
    const list    = document.getElementById('qb-subject-list');
    const empty   = document.getElementById('qb-subject-empty');
    const newInp  = document.getElementById('qb-subject-new');
    const addBtn  = document.getElementById('qb-subject-add');
    const hidden  = document.getElementById('qb-subject-value');
    const labelEl = document.getElementById('qb-subject-label');

    if (!btn) return;

    // Toggle panel
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const isHidden = panel.classList.contains('hidden');
        panel.classList.toggle('hidden', !isHidden);
        if (isHidden) { search.value = ''; search.focus(); filterList(''); }
    });

    // Đóng khi click ngoài
    document.addEventListener('click', () => panel.classList.add('hidden'));
    panel.addEventListener('click', (e) => e.stopPropagation());

    // Search filter
    function filterList(q) {
        const items = list.querySelectorAll('.qb-subject-option');
        let visible = 0;
        items.forEach(item => {
            const match = item.dataset.value.toLowerCase().includes(q.toLowerCase());
            item.classList.toggle('hidden', !match);
            if (match) visible++;
        });
        empty.classList.toggle('hidden', visible > 0);
    }
    search.addEventListener('input', () => filterList(search.value));

    // Chọn option
    list.addEventListener('click', (e) => {
        const item = e.target.closest('.qb-subject-option');
        if (!item) return;
        selectSubject(item.dataset.value);

        // Trigger topic update nếu có
        const topicSelect = document.querySelector('[data-question-topic-select]');
        if (topicSelect) {
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    // Thêm môn mới
    function addNew() {
        const val = newInp.value.trim();
        if (!val) return;
        // Kiểm tra trùng
        const exists = [...list.querySelectorAll('.qb-subject-option')]
            .some(el => el.dataset.value.toLowerCase() === val.toLowerCase());
        if (!exists) {
            const li = document.createElement('li');
            li.className = 'qb-subject-option cursor-pointer rounded-xl px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-green-50 hover:text-green-700';
            li.dataset.value = val;
            li.textContent = val;
            list.insertBefore(li, empty);
        }
        newInp.value = '';
        selectSubject(val);
    }
    addBtn.addEventListener('click', addNew);
    newInp.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); addNew(); } });

    function selectSubject(value) {
        hidden.value = value;
        labelEl.textContent = value;
        labelEl.classList.remove('text-slate-400');
        labelEl.classList.add('text-slate-800');

        list.querySelectorAll('.qb-subject-option').forEach(el => {
            el.classList.toggle('bg-green-50', el.dataset.value === value);
            el.classList.toggle('text-green-700', el.dataset.value === value);
        });

        panel.classList.add('hidden');
        filterList('');
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
    }
})();
</script>
