{{-- student-exam::result --}}
@extends('Mindigo-dashboard::layouts')

@section('title', __('student-exam::app.your_result') . ' · Mindigo LMS')

@section('styles')
    @vite([
        'packages/Mindigo/Dashboard/src/resources/css/app.css',
        'packages/Mindigo/Dashboard/src/resources/js/app.js',
    ])
@endsection

@section('content')
@php
    /**
     * $result từ ExamService::getResult():
     * {
     *   exam, score, max_score, percentage, passed,
     *   show_review (bool),
     *   questions   (Collection — chỉ có khi show_review),
     *   answers     (Collection keyBy question_id — chỉ có khi show_review)
     * }
     */
    $exam       = $result['exam'];
    $score      = $result['score'];
    $maxScore   = $result['max_score'];
    $percentage = round($result['percentage'] ?? 0, 1);
    $passed     = $result['passed'];   // true | false | null (chờ chấm)

    $duration = null;
    if ($attempt->started_at && $attempt->submitted_at) {
        $duration = \Carbon\Carbon::parse($attempt->started_at)
            ->diffInMinutes(\Carbon\Carbon::parse($attempt->submitted_at));
    }
@endphp

<div class="mx-auto max-w-3xl px-4 py-8 space-y-6">

    {{-- ── Score card ── --}}
    <div class="relative overflow-hidden rounded-2xl border bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800">

        {{-- Decorative ring --}}
        <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full opacity-60
            {{ $passed === null ? 'bg-amber-50 dark:bg-amber-900/20'
             : ($passed         ? 'bg-emerald-50 dark:bg-emerald-900/20'
                                : 'bg-red-50 dark:bg-red-900/20') }}">
        </div>

        <div class="relative flex flex-col items-center text-center">

            {{-- Status icon --}}
            @if($passed === null)
                <span class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                    <svg class="h-7 w-7 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
                    </svg>
                </span>
                <p class="text-lg font-semibold text-amber-700 dark:text-amber-300">@lang('student-exam::app.pending_review')</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('student-exam::app.essay_pending')</p>

            @elseif($passed)
                <span class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                    <svg class="h-7 w-7 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <p class="text-lg font-semibold text-emerald-700 dark:text-emerald-300">@lang('student-exam::app.passed')</p>

            @else
                <span class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-500 dark:bg-red-900/40 dark:text-red-400">
                    <svg class="h-7 w-7 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <p class="text-lg font-semibold text-red-600 dark:text-red-400">@lang('student-exam::app.failed')</p>
            @endif

            {{-- Score display --}}
            @if($score !== null)
            <div class="mt-5 flex items-end gap-1">
                <span class="text-5xl font-extrabold tabular-nums text-gray-900 dark:text-white">{{ $percentage }}</span>
                <span class="mb-2 text-xl font-semibold text-gray-400">%</span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $score }} / {{ $maxScore }} @lang('student-exam::app.score_label')
            </p>
            @endif

            {{-- Meta --}}
            <div class="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs text-gray-400 dark:text-gray-500">
                @if($attempt->submitted_at)
                    <span>@lang('student-exam::app.submitted_at'): {{ \Carbon\Carbon::parse($attempt->submitted_at)->format('d/m/Y H:i') }}</span>
                @endif
                @if($duration !== null)
                    <span>@lang('student-exam::app.duration_label'): {{ $duration }} @lang('student-exam::app.minutes')</span>
                @endif
                @if($exam->passing_percentage)
                    <span>@lang('student-exam::app.passing_score'): {{ $exam->passing_percentage }}%</span>
                @endif
                @if($attempt->tab_leave_count)
                    <span class="text-amber-500">@lang('student-exam::app.tab_leave_label'): {{ $attempt->tab_leave_count }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Review section ── --}}
    @if($result['show_review'] && $result['questions']->isNotEmpty())
    <section>
        <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">
            @lang('student-exam::app.review_answers')
        </h2>

        <div class="space-y-4">
            @foreach($result['questions'] as $index => $question)
            @php
                $qId      = $question->id;
                $answered = $result['answers'][$qId] ?? null;
                $answerVal= $answered?->answer_value;
                // BE type: 'single' | 'multiple_choice' | 'essay'
                $qType    = $question->type ?? 'single';
                // options là Eloquent Collection
                $options  = $question->options ?? collect();

                $correctIds = $options->where('is_correct', true)->pluck('id')
                    ->map(fn($v) => (string)$v)->toArray();
                $hasOptions = $options->isNotEmpty() && !empty($correctIds);

                if (!$hasOptions) {
                    $isCorrect = null; // essay — chờ GV chấm
                } elseif ($qType === 'multiple_choice') {
                    $givenIds = ($answerVal && str_starts_with($answerVal, '['))
                        ? array_map('strval', json_decode($answerVal, true))
                        : [];
                    sort($givenIds); sort($correctIds);
                    $isCorrect = ($givenIds === $correctIds);
                } else {
                    $isCorrect = (string)$answerVal === (string)($correctIds[0] ?? null);
                }
            @endphp

            <div class="rounded-2xl border bg-white p-5 dark:border-gray-700 dark:bg-gray-800
                {{ $isCorrect === true  ? 'border-l-4 border-l-emerald-500'
                 : ($isCorrect === false ? 'border-l-4 border-l-red-400'
                 : 'border-l-4 border-l-amber-400') }}">

                {{-- Question --}}
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="text-sm font-medium text-gray-900 dark:text-white leading-relaxed">
                        <span class="mr-2 text-gray-400">{{ $index + 1 }}.</span>{!! $question->content !!}
                    </div>
                    @if($isCorrect === true)
                        <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            @lang('student-exam::app.correct')
                        </span>
                    @elseif($isCorrect === false)
                        <span class="shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-600 dark:bg-red-900/40 dark:text-red-300">
                            @lang('student-exam::app.incorrect')
                        </span>
                    @else
                        <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                            @lang('student-exam::app.status_pending_review')
                        </span>
                    @endif
                </div>

                {{-- Options (trắc nghiệm) --}}
                @if($hasOptions)
                <div class="space-y-1.5">
                    @foreach($options as $option)
                    @php
                        $isCorrectOpt = in_array((string)$option->id, $correctIds);

                        if ($qType === 'multiple_choice') {
                            $givenArr = ($answerVal && str_starts_with($answerVal, '['))
                                ? array_map('strval', json_decode($answerVal, true)) : [];
                            $isChosen = in_array((string)$option->id, $givenArr);
                        } else {
                            $isChosen = (string)$answerVal === (string)$option->id;
                        }
                    @endphp
                    <div class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm
                        {{ $isCorrectOpt && $isChosen   ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : '' }}
                        {{ !$isCorrectOpt && $isChosen  ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                        {{ $isCorrectOpt && !$isChosen  ? 'border border-dashed border-emerald-300 bg-emerald-50/50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : '' }}
                        {{ !$isCorrectOpt && !$isChosen ? 'text-gray-500 dark:text-gray-400' : '' }}">

                        @if($isCorrectOpt && $isChosen)
                            <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                        @elseif(!$isCorrectOpt && $isChosen)
                            <svg class="h-4 w-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                        @elseif($isCorrectOpt)
                            <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75"/></svg>
                        @else
                            <span class="h-4 w-4 shrink-0"></span>
                        @endif

                        {{ $option->content }}
                    </div>
                    @endforeach
                </div>

                {{-- Essay --}}
                @else
                <div class="rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                    @if($answerVal)
                        <p class="mb-1 text-xs font-medium text-gray-400">@lang('student-exam::app.your_answer')</p>
                        <p class="whitespace-pre-wrap">{{ $answerVal }}</p>
                    @else
                        <p class="italic text-gray-400">@lang('student-exam::app.no_answer')</p>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </section>

    @elseif(!$result['show_review'])
    <div class="rounded-2xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500">
        @lang('student-exam::app.no_review_available')
    </div>
    @endif

    {{-- Back link --}}
    <div class="text-center">
        <a href="{{ route('student.exams.index') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 no-underline transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            @lang('student-exam::app.back_to_exams')
        </a>
    </div>

</div>
@endsection