@extends('Mindigo-dashboard::layouts')

@section('title', $exam->title)

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/ExamManagement/src/resources/css/app.css','packages/Mindigo/ExamManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="exam-take-page" data-exam-attempt data-autosave-url="{{ route('exams.attempts.autosave', $attempt) }}" data-violation-url="{{ route('exams.attempts.violation', $attempt) }}" data-expires-at="{{ $attempt->expires_at?->toIso8601String() }}" data-saved-label="@lang('Mindigo-exam-management::app.autosave_saved')" data-save-error-label="@lang('Mindigo-exam-management::app.autosave_failed')">
    <header class="exam-take-header">
        <div class="min-w-0"><h1>{{ $exam->title }}</h1><p>{{ $questions->count() }} @lang('Mindigo-exam-management::app.questions') / {{ $exam->duration_minutes }} @lang('Mindigo-exam-management::app.minutes')</p></div>
        <div class="exam-timer" data-exam-timer>--:--</div>
    </header>

    <form method="POST" action="{{ route('exams.attempts.submit', $attempt) }}" class="exam-take-layout" data-exam-form>
        @csrf
        <aside class="exam-question-nav">
            @foreach($questions as $index => $question)
                <a href="#q{{ $question->id }}" class="exam-question-nav-item">{{ $index + 1 }}</a>
            @endforeach
        </aside>

        <main class="flex min-w-0 flex-col gap-5">
            @foreach($questions as $index => $question)
                @php
                    $saved = (array) ($savedAnswers[$question->id] ?? $savedAnswers[(string) $question->id] ?? []);
                    $options = collect($question->options ?? []);
                    if ($exam->shuffle_answers && in_array($question->type, ['single_choice', 'multiple_choice'], true)) {
                        $options = $options->sortBy(fn ($value) => crc32($attempt->id . ':' . $question->id . ':' . $value))->values();
                    }
                    $options = $options->all();
                @endphp
                <section class="exam-answer-card" id="q{{ $question->id }}">
                    <div class="flex items-start justify-between gap-4">
                        <div><span class="exam-badge exam-type">@lang('Mindigo-exam-management::app.question_types.' . $question->type)</span><h2>{{ $index + 1 }}. {{ $question->content }}</h2></div>
                        <strong class="exam-point-pill">{{ number_format((float) $question->points, 2) }}</strong>
                    </div>

                    <div class="mt-5 grid gap-3">
                        @if($question->type === 'single_choice' || $question->type === 'true_false')
                            @foreach($options as $option)
                                <label class="exam-choice"><input type="radio" name="answers[{{ $question->id }}][]" value="{{ $option }}" @checked(in_array($option, $saved, true))><span></span><strong>{{ $option }}</strong></label>
                            @endforeach
                        @elseif($question->type === 'multiple_choice')
                            @foreach($options as $option)
                                <label class="exam-choice"><input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option }}" @checked(in_array($option, $saved, true))><span></span><strong>{{ $option }}</strong></label>
                            @endforeach
                        @elseif($question->type === 'short_answer')
                            <input name="answers[{{ $question->id }}][]" value="{{ $saved[0] ?? '' }}" class="exam-input" placeholder="@lang('Mindigo-exam-management::app.short_answer_placeholder')">
                        @else
                            <textarea name="answers[{{ $question->id }}][]" class="exam-textarea" placeholder="@lang('Mindigo-exam-management::app.essay_placeholder')">{{ $saved[0] ?? '' }}</textarea>
                        @endif
                    </div>
                </section>
            @endforeach

            <div class="exam-submit-bar">
                <span data-autosave-status>@lang('Mindigo-exam-management::app.autosave_ready')</span>
                <button type="submit" class="exam-primary-button" data-mindigo-confirm-title="@lang('Mindigo-exam-management::app.confirm_submit_title')" data-mindigo-confirm-message="@lang('Mindigo-exam-management::app.confirm_submit_message')" data-mindigo-confirm-text="@lang('Mindigo-exam-management::app.submit_exam')" data-mindigo-confirm-cancel="@lang('Mindigo-exam-management::app.cancel')">@lang('Mindigo-exam-management::app.submit_exam')</button>
            </div>
        </main>
    </form>
</div>
@endsection
