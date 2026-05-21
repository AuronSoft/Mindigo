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
    <section class="question-builder-card">
        <div class="question-section-head">
            <span>01</span>
            <div>
                <h2>@lang('Mindigo-question-bank::app.section_content')</h2>
                <p>@lang('Mindigo-question-bank::app.section_content_desc')</p>
            </div>
        </div>

        <div class="question-form-grid mt-5">
            <label class="question-field">
                <span>@lang('Mindigo-question-bank::app.subject')</span>
                <input name="subject" value="{{ old('subject', $question->subject ?? '') }}" class="question-input" list="question-subjects" required>
                <datalist id="question-subjects">
                    @foreach($subjects as $subject)
                        <option value="{{ $subject }}"></option>
                    @endforeach
                </datalist>
                @error('subject')<strong>{{ $message }}</strong>@enderror
            </label>

            <label class="question-field">
                <span>@lang('Mindigo-question-bank::app.topic')</span>
                <input name="topic" value="{{ old('topic', $question->topic ?? '') }}" class="question-input">
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

    <section class="question-builder-card" data-option-panel>
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

    <section class="question-builder-card">
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
