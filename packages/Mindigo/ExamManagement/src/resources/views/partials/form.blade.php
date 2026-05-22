@php
    $editing = isset($exam);
    $config = old('generation_config', $exam->generation_config ?? []);
    $counts = old('counts', $config['counts'] ?? ['single_choice' => 20, 'multiple_choice' => 0, 'true_false' => 0, 'short_answer' => 0]);
    $points = old('points', $config['points'] ?? ['single_choice' => 1, 'multiple_choice' => 1, 'true_false' => 1, 'short_answer' => 1]);
@endphp

<section class="exam-builder-card">
    <div class="exam-section-head"><span>01</span><div><h2>@lang('Mindigo-exam-management::app.basic_info')</h2><p>@lang('Mindigo-exam-management::app.basic_info_desc')</p></div></div>
    <div class="exam-form-grid mt-5">
        <label class="exam-field md:col-span-2"><span>@lang('Mindigo-exam-management::app.title_field')</span><input name="title" value="{{ old('title', $exam->title ?? '') }}" class="exam-input" required></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.subject')</span><input name="subject" value="{{ old('subject', $exam->subject ?? '') }}" class="exam-input" list="exam-subjects"></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.topic')</span><input name="topic" value="{{ old('topic', $exam->topic ?? '') }}" class="exam-input"></label>
        <label class="exam-field md:col-span-2"><span>@lang('Mindigo-exam-management::app.description')</span><textarea name="description" class="exam-textarea">{{ old('description', $exam->description ?? '') }}</textarea></label>
    </div>
    <datalist id="exam-subjects">@foreach($subjects as $subject)<option value="{{ $subject }}"></option>@endforeach</datalist>
</section>

<section class="exam-builder-card">
    <div class="exam-section-head"><span>02</span><div><h2>@lang('Mindigo-exam-management::app.runtime')</h2><p>@lang('Mindigo-exam-management::app.runtime_desc')</p></div></div>
    <div class="exam-form-grid mt-5">
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.duration_minutes')</span><input type="number" min="1" max="600" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?? 45) }}" class="exam-input" required></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.max_attempts')</span><input type="number" min="1" max="20" name="max_attempts" value="{{ old('max_attempts', $exam->max_attempts ?? 1) }}" class="exam-input" required></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.passing_score')</span><input type="number" min="0" step="0.25" name="passing_score" value="{{ old('passing_score', $exam->passing_score ?? 0) }}" class="exam-input" required></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.starts_at')</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($exam) && $exam->starts_at ? $exam->starts_at->format('Y-m-d\TH:i') : '') }}" class="exam-input"></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.ends_at')</span><input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($exam) && $exam->ends_at ? $exam->ends_at->format('Y-m-d\TH:i') : '') }}" class="exam-input"></label>
        <div class="exam-toggle-grid md:col-span-2">
            @foreach(['shuffle_questions', 'shuffle_answers', 'show_results'] as $toggle)
                <label class="exam-toggle"><input type="checkbox" name="{{ $toggle }}" value="1" @checked(old($toggle, $exam->{$toggle} ?? true))><span></span><strong>@lang('Mindigo-exam-management::app.' . $toggle)</strong></label>
            @endforeach
        </div>
    </div>
</section>

<section class="exam-builder-card">
    <div class="exam-section-head"><span>03</span><div><h2>@lang('Mindigo-exam-management::app.generation')</h2><p>@lang('Mindigo-exam-management::app.generation_desc')</p></div></div>
    <div class="exam-form-grid mt-5">
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.folder')</span><select name="folder_id" class="exam-select"><option value="">@lang('Mindigo-exam-management::app.any_folder')</option>@foreach($folders as $folder)<option value="{{ $folder->id }}" @selected((string) old('folder_id', $config['folder_id'] ?? '') === (string) $folder->id)>{{ $folder->name }} ({{ $folder->questions_count }})</option>@endforeach</select></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.generation_subject')</span><input name="generation_subject" value="{{ old('generation_subject', $config['subject'] ?? '') }}" class="exam-input" list="exam-subjects"></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.generation_topic')</span><input name="generation_topic" value="{{ old('generation_topic', $config['topic'] ?? '') }}" class="exam-input"></label>
        <label class="exam-field"><span>@lang('Mindigo-exam-management::app.generation_difficulty')</span><select name="generation_difficulty" class="exam-select"><option value="">@lang('Mindigo-exam-management::app.any_difficulty')</option>@foreach($difficulties as $difficulty)<option value="{{ $difficulty }}" @selected(old('generation_difficulty', $config['difficulty'] ?? '') === $difficulty)>@lang('Mindigo-exam-management::app.difficulties.' . $difficulty)</option>@endforeach</select></label>
    </div>

    <div class="exam-type-grid mt-5">
        @foreach($types as $type)
            <article class="exam-type-card">
                <strong>@lang('Mindigo-exam-management::app.question_types.' . $type)</strong>
                <label><span>@lang('Mindigo-exam-management::app.count')</span><input type="number" min="0" max="200" name="counts[{{ $type }}]" value="{{ $counts[$type] ?? 0 }}" class="exam-input"></label>
                <label><span>@lang('Mindigo-exam-management::app.points')</span><input type="number" min="0" max="100" step="0.25" name="points[{{ $type }}]" value="{{ $points[$type] ?? 1 }}" class="exam-input"></label>
            </article>
        @endforeach
    </div>

    @if($editing)
        <label class="exam-toggle mt-5"><input type="checkbox" name="regenerate_questions" value="1"><span></span><strong>@lang('Mindigo-exam-management::app.regenerate_questions')</strong></label>
    @endif
</section>
