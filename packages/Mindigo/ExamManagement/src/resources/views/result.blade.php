@extends('Mindigo-dashboard::layouts')

@section('title', __('Mindigo-exam-management::app.result'))

@section('styles')
    @vite(['packages/Mindigo/Dashboard/src/resources/css/app.css','packages/Mindigo/Dashboard/src/resources/js/app.js','packages/Mindigo/ExamManagement/src/resources/css/app.css','packages/Mindigo/ExamManagement/src/resources/js/app.js'])
@endsection

@section('content')
<div class="exam-page mx-auto flex max-w-6xl flex-col gap-6">
    <header class="exam-hero">
        <div><div class="exam-breadcrumb"><a href="{{ route('dashboard') }}">@lang('Mindigo-dashboard::app.dashboard')</a><span>/</span><a href="{{ route('exams.show', $exam) }}">{{ $exam->title }}</a><span>/</span><strong>@lang('Mindigo-exam-management::app.result')</strong></div><h1>@lang('Mindigo-exam-management::app.result')</h1><p>{{ $exam->title }}</p></div>
        <a href="{{ route('exams.show', $exam) }}" class="exam-secondary-button">@lang('Mindigo-exam-management::app.back')</a>
    </header>

    <section class="grid gap-4 md:grid-cols-4">
        <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.score')</span><strong>{{ number_format((float) $attempt->score, 2) }}</strong></article>
        <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.max_score')</span><strong>{{ number_format((float) $attempt->max_score, 2) }}</strong></article>
        <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.percentage')</span><strong>{{ number_format((float) $attempt->percentage, 2) }}%</strong></article>
        <article class="exam-stat-card"><span>@lang('Mindigo-exam-management::app.passed')</span><strong class="text-xl">{{ $attempt->passed ? __('Mindigo-exam-management::app.yes') : __('Mindigo-exam-management::app.no') }}</strong></article>
    </section>

    @if($exam->show_results || auth()->user()?->hasPermissionTo('exams.view'))
        <section class="exam-card overflow-hidden">
            <div class="exam-card-head"><h2>@lang('Mindigo-exam-management::app.answer_detail')</h2><p>@lang('Mindigo-exam-management::app.answer_detail_desc')</p></div>
            <div class="divide-y divide-slate-100">
                @foreach($attempt->answers as $answer)
                    <article class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0"><span class="exam-badge {{ $answer->needs_review ? 'exam-status-reviewing' : ($answer->is_correct ? 'exam-status-published' : 'exam-status-closed') }}">{{ $answer->needs_review ? __('Mindigo-exam-management::app.pending_review') : ($answer->is_correct ? __('Mindigo-exam-management::app.correct') : __('Mindigo-exam-management::app.incorrect')) }}</span><p class="mt-2 text-sm font-black leading-6 text-slate-900">{{ $answer->question?->content }}</p></div>
                            <strong class="exam-point-pill">{{ number_format((float) $answer->points_awarded, 2) }}</strong>
                        </div>
                        <div class="mt-3 grid gap-2 text-sm font-semibold text-slate-500">
                            <p><strong>@lang('Mindigo-exam-management::app.your_answer'):</strong> {{ implode(', ', $answer->answer ?? []) ?: '-' }}</p>
                            <p><strong>@lang('Mindigo-exam-management::app.correct_answers'):</strong> {{ implode(', ', $answer->question?->correct_answers ?? []) ?: '-' }}</p>
                            @if($answer->question?->explanation)<p><strong>@lang('Mindigo-exam-management::app.explanation'):</strong> {{ $answer->question->explanation }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
